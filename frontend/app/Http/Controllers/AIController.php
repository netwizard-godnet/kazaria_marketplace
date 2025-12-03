<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class AIController extends Controller
{
    public function query(Request $request)
    {
        try {
            if (!config('kazar_ai.enabled')) {
                return response()->json(['success' => false, 'message' => 'KAZAR I.A désactivée'], 403);
            }

            $text = (string) ($request->input('message') ?? '');
            if (trim($text) === '') {
                return response()->json(['success' => false, 'message' => 'Message vide'], 422);
            }

        // Extraction enrichie
        [$priceMin, $priceMax] = $this->extractPriceRange($text);
        $storageGb = $this->extractNumber($text, '(?:stockage|mémoire\s*interne|rom|go|gb)');
        $ramGb = $this->extractNumber($text, '(?:ram|mémoire\s*vive)');
        $brand = $this->extractBrand($text);
        $category = $this->extractCategory($text); // phone, laptop, tv, fridge, freezer, kettle ...
        // Essayez de résoudre dynamiquement une catégorie/sous-catégorie depuis la BDD
        [$resolvedCategoryId, $resolvedSubcategoryId] = $this->resolveCategoryFromDatabase($text);
        $requestedKeywords = $this->extractProductKeywords($text); // ex: ['bouilloire']
        $hasDemand = ($category !== null) || ($brand !== null) || ($storageGb !== null) || ($ramGb !== null)
            || ($priceMin !== null) || ($priceMax !== null) || (!empty($requestedKeywords));

        // Small-talk: nom, présentation, état
        $tLower = mb_strtolower($text);
        // Capture du prénom de l'utilisateur : "je m'appelle X"
        if (preg_match("/je m[’' ]appelle\s+([a-zà-ÿ\- ]{2,30})/i", $text, $m)) {
            $name = trim($m[1]);
            $name = preg_replace('/\s+/', ' ', $name);
            $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
            session(['ai_user_name' => $name]);
            return response()->json([
                'success' => true,
                'message' => "Enchanté, $name ! Je suis KAZAR I.A. Dites‑moi votre besoin et je vous accompagne.",
                'items' => [],
                'intent' => 'smalltalk_set_name',
                'intent_params' => ['name' => $name],
                'understood' => []
            ]);
        }
        $userName = session('ai_user_name');
        if (preg_match("/(tu t'appelles comment|ton nom|qui es-tu|comment tu t'appelles|c'est qui|qui es tu)/i", $text)) {
            $msg = $userName ? ("Ravi de vous revoir, $userName ! ") : '';
            $msg .= "Je suis KAZAR I.A, l’assistant de KAZARIA. Dites‑moi votre besoin et je m’occupe du reste !";
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_name',
                'intent_params' => [],
                'understood' => []
            ]);
        }
        if (preg_match('/comment ça va|ça va|sa va|comment vas-tu/i', $tLower)) {
            $msg = $userName ? ("Ça va très bien, merci $userName ! ") : "Très bien, merci ! ";
            $msg .= "Je suis là pour vous aider avec vos achats sur KAZARIA.";
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_status',
                'intent_params' => [],
                'understood' => []
            ]);
        }
        // Merci / au revoir / salutations
        if (preg_match('/\bmerci\b/i', $text)) {
            $msg = $userName ? ("Avec plaisir, $userName ! Besoin d’autre chose ?") : "Avec plaisir ! Besoin d’autre chose ?";
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_thanks',
                'intent_params' => [],
                'understood' => []
            ]);
        }
        if (preg_match('/au revoir|à bientôt|a bientôt|a plus|à plus/i', $tLower)) {
            $msg = $userName ? ("À bientôt, $userName !") : "À bientôt !";
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_bye',
                'intent_params' => [],
                'understood' => []
            ]);
        }
        if (preg_match('/\b(bonjour|salut|bonsoir)\b/i', $tLower)) {
            $msg = $userName ? ("Bonjour $userName ! ") : "Bonjour ! ";
            $msg .= "Dites‑moi votre besoin (budget, catégorie, marque…) et je vous ferai une sélection.";
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_greeting',
                'intent_params' => [],
                'understood' => []
            ]);
        }

        // Si l'utilisateur salue ou ne donne aucun critère, ne pas proposer d'articles arbitraires
        if (!$hasDemand) {
            $msg = $userName ? ("Bonjour $userName ! ") : "Bonjour ! ";
            $msg .= "Dites‑moi votre besoin (budget, catégorie, marque, etc.) et je vous ferai une sélection.";
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'greeting',
                'intent_params' => [],
                'understood' => []
            ]);
        }
        $isPhone = $category === 'phone';

        // Nouveaux attributs
        $color = $this->extractColor($text);
        $screenInch = $this->extractScreenInch($text);
        $batteryMah = $this->extractBattery($text);
        $cameraMp = $this->extractCameraMp($text);
        $needsDualSim = $this->extractDualSim($text);
        $needs5g = $this->extract5g($text);
        // Extra: fréquence écran, NFC, eSIM, étanchéité IP, cam selfie/ultra‑wide
        $refreshHz = $this->extractRefreshHz($text);
        $hasNfc = $this->extractNfc($text);
        $hasEsim = $this->extractEsim($text);
        $ipRating = $this->extractIpRating($text); // 67/68
        $selfieMp = $this->extractSelfieMp($text);
        $ultraWideMp = $this->extractUltraWideMp($text);

        // Requête produits
        $q = Product::query()->active();
        if ($category) {
            $q->whereHas('category', function ($q2) use ($category) {
                if ($category === 'phone') {
                    $q2->where(function ($qq) {
                        $qq->where('name', 'like', '%téléphone%')
                           ->orWhere('name', 'like', '%smartphone%');
                    });
                } elseif ($category === 'laptop') {
                    $q2->where(function ($qq) {
                        $qq->where('name', 'like', '%ordinateur%')
                           ->orWhere('name', 'like', '%laptop%');
                    });
                } elseif ($category === 'tv') {
                    $q2->where(function ($qq) {
                        $qq->where('name', 'like', '%tv%')
                           ->orWhere('name', 'like', '%télévision%');
                    });
                } elseif ($category === 'fridge') {
                    $q2->where(function ($qq) {
                        $qq->where('name', 'like', '%réfrigérateur%')
                           ->orWhere('name', 'like', '%frigo%');
                    });
                } elseif ($category === 'kettle') {
                    $q2->where(function ($qq) {
                        $qq->where('name', 'like', '%bouilloire%')
                           ->orWhere('name', 'like', '%cuisine%')
                           ->orWhere('name', 'like', '%électroménager%');
                    });
                } elseif ($category === 'freezer') {
                    $q2->where(function ($qq) {
                        $qq->where('name', 'like', '%congélateur%')
                           ->orWhere('name', 'like', '%congelateur%')
                           ->orWhere('name', 'like', '%freezer%');
                    });
                }
            });
        }
        if ($resolvedCategoryId) {
            $q->where('category_id', $resolvedCategoryId);
        }
        if ($resolvedSubcategoryId) {
            $q->where('subcategory_id', $resolvedSubcategoryId);
        }
        // Si téléphone demandé, exclure explicitement les tablettes
        if ($category === 'phone' || $isPhone) {
            $q->where(function ($qq) {
                $qq->whereRaw('LOWER(name) NOT LIKE ?', ['%tablette%'])
                   ->whereRaw('LOWER(name) NOT LIKE ?', ['%tab %'])
                   ->whereRaw('LOWER(name) NOT LIKE ?', ['%ipad%'])
                   ->whereRaw('LOWER(name) NOT LIKE ?', ['%matepad%'])
                   ->whereRaw('LOWER(description) NOT LIKE ?', ['%tablette%']);
            });
        }
        // Si mot-clé produit explicite (ex: bouilloire), filtrer par nom/description/tags
        // Sauf si le mot-clé est redondant avec la catégorie détectée (ex: "congélateur" avec category=freezer)
        if (!empty($requestedKeywords) && !$this->areKeywordsRedundantWithCategory($requestedKeywords, $category)) {
            $q->where(function ($qq) use ($requestedKeywords) {
                foreach ($requestedKeywords as $kw) {
                    $qq->orWhere('name', 'like', "%{$kw}%")
                       ->orWhere('description', 'like', "%{$kw}%")
                       ->orWhere('tags', 'like', "%{$kw}%");
                }
            });
        }
        if ($priceMin !== null) {
            $q->where('price', '>=', $priceMin);
        }
        if ($priceMax !== null) {
            $q->where('price', '<=', $priceMax);
        }
        if ($priceMin === null && $priceMax === null) {
            $q->orderBy('discount_percentage', 'desc')->orderBy('price');
        } else {
            $q->orderBy('price');
        }
        // Filtre stockage via attributs si disponibles (best-effort) - plus flexible
        if ($storageGb) {
            $q->where(function ($qq) use ($storageGb) {
                // Formats variés : 128GB, 128 GB, 128Go, 128 Go, etc. (tolérance espace)
                $qq->where('name', 'like', "%{$storageGb}GB%")
                   ->orWhere('name', 'like', "%{$storageGb} GB%")
                   ->orWhere('name', 'like', "%{$storageGb}Go%")
                   ->orWhere('name', 'like', "%{$storageGb} Go%")
                   ->orWhere('name', 'like', "%{$storageGb}go%")
                   ->orWhere('name', 'like', "%{$storageGb} go%")
                   ->orWhere('description', 'like', "%{$storageGb}GB%")
                   ->orWhere('description', 'like', "%{$storageGb} GB%")
                   ->orWhere('description', 'like', "%{$storageGb}Go%")
                   ->orWhere('description', 'like', "%{$storageGb} Go%")
                   ->orWhere('description', 'like', "%{$storageGb}go%")
                   ->orWhere('description', 'like', "%{$storageGb} go%")
                   ->orWhere('tags', 'like', "%{$storageGb}%");
            });
        }
        if ($ramGb) {
            $q->where(function ($qq) use ($ramGb) {
                $qq->where('name', 'like', "%{$ramGb}GB RAM%")
                   ->orWhere('name', 'like', "%{$ramGb} Go RAM%")
                   ->orWhere('description', 'like', "%{$ramGb}GB RAM%")
                   ->orWhere('description', 'like', "%{$ramGb} Go RAM%")
                   ->orWhere('tags', 'like', "%{$ramGb} RAM%");
            });
        }
        if ($brand) {
            $q->where(function ($qq) use ($brand) {
                $qq->where('brand', 'like', "%{$brand}%")
                   ->orWhere('name', 'like', "%{$brand}%");
            });
        }

        if ($color) {
            $syn = $this->colorSynonyms($color);
            $q->where(function ($qq) use ($syn) {
                foreach ($syn as $c) {
                    $qq->orWhere('name', 'like', "%{$c}%")
                       ->orWhere('description', 'like', "%{$c}%")
                       ->orWhere('tags', 'like', "%{$c}%");
                }
            });
        }
        if ($screenInch) {
            $needle = str_replace(',', '.', $screenInch);
            $q->where(function ($qq) use ($needle) {
                $qq->where('name', 'like', "%{$needle}%")
                   ->orWhere('description', 'like', "%{$needle}%");
            });
        }
        if ($batteryMah) {
            $q->where(function ($qq) use ($batteryMah) {
                $qq->where('name', 'like', "%{$batteryMah}mAh%")
                   ->orWhere('description', 'like', "%{$batteryMah} mAh%")
                   ->orWhere('tags', 'like', "%{$batteryMah}mAh%");
            });
        }
        if ($cameraMp) {
            $q->where(function ($qq) use ($cameraMp) {
                $qq->where('name', 'like', "%{$cameraMp}MP%")
                   ->orWhere('name', 'like', "%{$cameraMp} Mpx%")
                   ->orWhere('description', 'like', "%{$cameraMp}MP%")
                   ->orWhere('description', 'like', "%{$cameraMp} Mpx%");
            });
        }
        if ($needsDualSim) {
            $q->where(function ($qq) {
                $qq->where('name', 'like', '%dual sim%')
                   ->orWhere('name', 'like', '%double sim%')
                   ->orWhere('description', 'like', '%dual sim%')
                   ->orWhere('description', 'like', '%double sim%');
            });
        }
        if ($needs5g) {
            $q->where(function ($qq) {
                $qq->where('name', 'like', '%5g%')
                   ->orWhere('description', 'like', '%5g%');
            });
        }
        if ($refreshHz) {
            $needle = $refreshHz.'hz';
            $q->where(function ($qq) use ($needle) {
                $qq->where('name', 'like', "%{$needle}%")
                   ->orWhere('description', 'like', "%{$needle}%");
            });
        }
        if ($hasNfc) {
            $q->where(function ($qq) {
                $qq->where('name', 'like', '%nfc%')
                   ->orWhere('description', 'like', '%nfc%');
            });
        }
        if ($hasEsim) {
            $q->where(function ($qq) {
                $qq->where('name', 'like', '%esim%')
                   ->orWhere('description', 'like', '%e-sim%');
            });
        }
        if ($ipRating) {
            $needle = 'ip'.$ipRating;
            $q->where(function ($qq) use ($needle) {
                $qq->where('name', 'like', "%{$needle}%")
                   ->orWhere('description', 'like', "%{$needle}%");
            });
        }
        if ($selfieMp) {
            $q->where(function ($qq) use ($selfieMp) {
                $qq->where('name', 'like', "%{$selfieMp}MP selfie%")
                   ->orWhere('description', 'like', "%{$selfieMp}MP selfie%")
                   ->orWhere('description', 'like', "%{$selfieMp} Mpx selfie%");
            });
        }
        if ($ultraWideMp) {
            $q->where(function ($qq) use ($ultraWideMp) {
                $qq->where('description', 'like', "%{$ultraWideMp}% ultra%wide%")
                   ->orWhere('description', 'like', "%{$ultraWideMp}% grand%angle%")
                   ->orWhere('name', 'like', "%{$ultraWideMp}% ultra%wide%");
            });
        }

        // Intent + dispatcher
        [$intent, $intentParams] = $this->detectIntent($text);

        // Réponses FAQ générales (livraison, paiement, retours, garantie, contact, vendeur, horaires)
        if ($intent === 'qa') {
            $faqMsg = $this->answerGeneralQuestion($text);
            return response()->json([
                'success' => true,
                'message' => $faqMsg,
                'items' => [],
                'intent' => 'qa',
                'intent_params' => [],
                'understood' => []
            ]);
        }

        $actionResult = null;
        if ($intent === 'apply_coupon' && isset($intentParams['code'])) {
            // réutiliser le contrôleur coupon
            $couponReq = Request::create('/api/coupons/apply', 'POST', [
                'code' => $intentParams['code'],
                'subtotal' => max(0, $priceMax ?? 0), // valeur indicative
            ]);
            $actionResult = app()->call([\App\Http\Controllers\CouponController::class, 'apply'], ['request' => $couponReq]);
            $actionResult = $actionResult->getData(true);
        }

        $products = $q->limit(10)->get(['id','name','slug','image','price','old_price','discount_percentage','brand']);
        
        // Si aucun résultat avec filtres stricts, élargir la recherche progressivement
        if ($products->isEmpty() && ($category || !empty($requestedKeywords))) {
            // Niveau 2: Recherche dans nom/description des produits directement (sans filtre catégorie)
            $q2 = Product::query()->active();
            if ($category === 'freezer' || (!empty($requestedKeywords) && in_array('congélateur', $requestedKeywords))) {
                $q2->where(function($qq) {
                    $qq->where('name', 'like', '%congélateur%')
                       ->orWhere('name', 'like', '%congelateur%')
                       ->orWhere('name', 'like', '%freezer%')
                       ->orWhere('description', 'like', '%congélateur%')
                       ->orWhere('description', 'like', '%congelateur%')
                       ->orWhere('description', 'like', '%freezer%')
                       ->orWhere('tags', 'like', '%congélateur%');
                });
            } elseif ($category === 'kettle' || (!empty($requestedKeywords) && in_array('bouilloire', $requestedKeywords))) {
                $q2->where(function($qq) {
                    $qq->where('name', 'like', '%bouilloire%')
                       ->orWhere('description', 'like', '%bouilloire%')
                       ->orWhere('tags', 'like', '%bouilloire%');
                });
            } elseif ($category) {
                // Pour autres catégories, recherche directe dans nom/description
                $searchTerms = match($category) {
                    'phone' => ['téléphone', 'telephone', 'smartphone'],
                    'laptop' => ['ordinateur', 'laptop', 'pc'],
                    'tv' => ['tv', 'télévision', 'television'],
                    'fridge' => ['réfrigérateur', 'refrigerateur', 'frigo'],
                    default => []
                };
                if (!empty($searchTerms)) {
                    $q2->where(function($qq) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $qq->orWhere('name', 'like', "%{$term}%")
                               ->orWhere('description', 'like', "%{$term}%");
                        }
                    });
                }
            }
            // Appliquer les autres filtres (prix, marque, etc.) - mais pas stockage ici
            if ($priceMin !== null) $q2->where('price', '>=', $priceMin);
            if ($priceMax !== null) $q2->where('price', '<=', $priceMax);
            if ($brand) {
                $q2->where(function ($qq) use ($brand) {
                    $qq->where('brand', 'like', "%{$brand}%")
                       ->orWhere('name', 'like', "%{$brand}%");
                });
            }
            if ($priceMin === null && $priceMax === null) {
                $q2->orderBy('discount_percentage', 'desc')->orderBy('price');
            } else {
                $q2->orderBy('price');
            }
            $products = $q2->limit(10)->get(['id','name','slug','image','price','old_price','discount_percentage','brand']);
        }
        
        // Niveau 3: Si toujours vide avec catégorie + stockage, retirer le filtre stockage mais garder la catégorie
        if ($products->isEmpty() && $category && $storageGb) {
            $q3a = Product::query()->active();
            // Filtrer uniquement par catégorie (sans stockage)
            if ($category === 'phone') {
                $q3a->whereHas('category', function ($q2) {
                    $q2->where(function ($qq) {
                        $qq->where('name', 'like', '%téléphone%')
                           ->orWhere('name', 'like', '%smartphone%');
                    });
                })->whereRaw('LOWER(name) NOT LIKE ?', ['%tablette%']);
            } elseif ($category === 'freezer') {
                $q3a->where(function($qq) {
                    $qq->where('name', 'like', '%congélateur%')
                       ->orWhere('name', 'like', '%congelateur%')
                       ->orWhere('name', 'like', '%freezer%')
                       ->orWhere('description', 'like', '%congélateur%');
                });
            } elseif ($category) {
                $searchTerms = match($category) {
                    'laptop' => ['ordinateur', 'laptop', 'pc'],
                    'tv' => ['tv', 'télévision', 'television'],
                    'fridge' => ['réfrigérateur', 'refrigerateur', 'frigo'],
                    default => []
                };
                if (!empty($searchTerms)) {
                    $q3a->where(function($qq) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $qq->orWhere('name', 'like', "%{$term}%")
                               ->orWhere('description', 'like', "%{$term}%");
                        }
                    });
                }
            }
            // Appliquer prix/marque mais PAS stockage
            if ($priceMin !== null) $q3a->where('price', '>=', $priceMin);
            if ($priceMax !== null) $q3a->where('price', '<=', $priceMax);
            if ($brand) {
                $q3a->where(function ($qq) use ($brand) {
                    $qq->where('brand', 'like', "%{$brand}%")->orWhere('name', 'like', "%{$brand}%");
                });
            }
            $q3a->orderBy('discount_percentage', 'desc')->orderBy('price');
            $products = $q3a->limit(10)->get(['id','name','slug','image','price','old_price','discount_percentage','brand']);
        }
        
        // Si toujours vide, recherche large par mots-clés
        if ($products->isEmpty() && !empty($requestedKeywords)) {
            $q3 = Product::query()->active();
            foreach ($requestedKeywords as $kw) {
                $q3->where(function($qq) use ($kw) {
                    $qq->where('name', 'like', "%{$kw}%")
                       ->orWhere('description', 'like', "%{$kw}%")
                       ->orWhere('tags', 'like', "%{$kw}%");
                });
            }
            if ($priceMin !== null) $q3->where('price', '>=', $priceMin);
            if ($priceMax !== null) $q3->where('price', '<=', $priceMax);
            $q3->orderBy('discount_percentage', 'desc')->orderBy('price');
            $products = $q3->limit(10)->get(['id','name','slug','image','price','old_price','discount_percentage','brand']);
        }
        
        // Détecter la marque demandée dans le texte original (pour messages plus précis)
        $requestedBrandKeyword = null;
        $textLower = mb_strtolower($text);
        if (preg_match('/\bpixel\b/i', $text)) {
            $requestedBrandKeyword = 'pixel';
        } elseif ($brand) {
            $requestedBrandKeyword = $brand;
        }
        
        // Vérifier si la marque demandée correspond aux résultats
        $brandFound = true;
        if ($requestedBrandKeyword && !$products->isEmpty()) {
            $brandFound = false;
            foreach ($products as $p) {
                $pNameLower = mb_strtolower($p->name ?? '');
                $pBrandLower = mb_strtolower($p->brand ?? '');
                if ($requestedBrandKeyword === 'pixel') {
                    // Pour "pixel", chercher "pixel" ou "google" dans le nom/marque
                    if (str_contains($pNameLower, 'pixel') || str_contains($pBrandLower, 'google')) {
                        $brandFound = true;
                        break;
                    }
                } elseif ($pBrandLower === $requestedBrandKeyword || str_contains($pNameLower, $requestedBrandKeyword)) {
                    $brandFound = true;
                    break;
                }
            }
        }

        $reply = $this->buildReply($products, $priceMin, $priceMax, $storageGb, $ramGb, $brand, $category, $color, $screenInch, $batteryMah, $cameraMp, $needsDualSim, $needs5g, $refreshHz, $hasNfc, $hasEsim, $ipRating, $selfieMp, $ultraWideMp, $requestedBrandKeyword, $brandFound);

        return response()->json([
            'success' => true,
            'message' => $reply,
            'items' => $products,
            'intent' => $intent,
            'intent_params' => $intentParams,
            'action_result' => $actionResult,
            'understood' => [
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'storage_gb' => $storageGb,
                'ram_gb' => $ramGb,
                'brand' => $brand,
                'category' => $category,
                'color' => $color,
                'screen_inch' => $screenInch,
                'battery_mah' => $batteryMah,
                'camera_mp' => $cameraMp,
                'dual_sim' => $needsDualSim,
                'five_g' => $needs5g,
                'refresh_hz' => $refreshHz,
                'nfc' => $hasNfc,
                'esim' => $hasEsim,
                'ip_rating' => $ipRating,
                'selfie_mp' => $selfieMp,
                'ultra_wide_mp' => $ultraWideMp
            ]
        ]);
        } catch (\Throwable $e) {
            \Log::error('KAZAR I.A query error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur. Veuillez réessayer plus tard.'
            ], 500);
        }
    }

    public function logInteraction(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'type' => 'required|string|in:click,add_to_cart,purchase',
            'source' => 'nullable|string|max:20'
        ]);
        try {
            \App\Models\AIInteraction::create([
                'product_id' => (int)$validated['product_id'],
                'user_id' => optional($request->user())->id,
                'session_id' => $request->session()->getId(),
                'type' => $validated['type'],
                'source' => $validated['source'] ?? 'ai',
                'weight' => match($validated['type']) {
                    'click' => 1,
                    'add_to_cart' => 3,
                    'purchase' => 8,
                    default => 1
                }
            ]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::error('AI interaction log failed', ['e' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }
    }

    private function buildReply($products, $priceMin, $priceMax, $storageGb, $ramGb, $brand, $category, $color, $screenInch, $batteryMah, $cameraMp, $needsDualSim, $needs5g, $refreshHz, $hasNfc, $hasEsim, $ipRating, $selfieMp, $ultraWideMp, $requestedBrandKeyword = null, $brandFound = true)
    {
        if ($products->isEmpty()) {
            $msg = "Je n'ai pas trouvé d'article correspondant exactement.";
            if ($requestedBrandKeyword) {
                $displayBrand = ($requestedBrandKeyword === 'pixel') ? 'Pixel' : ucfirst($requestedBrandKeyword);
                $msg .= " Nous n'avons pas de " . $displayBrand . " pour le moment.";
            }
            $msg .= " Souhaitez‑vous élargir le budget ou voir d'autres marques similaires ?";
            return $msg;
        }
        
        // Si une marque était demandée mais pas trouvée dans les résultats
        if ($requestedBrandKeyword && !$brandFound) {
            $displayBrand = ($requestedBrandKeyword === 'pixel') ? 'Pixel' : ucfirst($requestedBrandKeyword);
            $msg = "Je n'ai pas trouvé de " . $displayBrand . " correspondant à vos critères.";
            $msg .= " Voici des alternatives similaires :";
            return $msg;
        }
        $intro = [];
        if ($priceMin !== null || $priceMax !== null) {
            if ($priceMin !== null && $priceMax !== null) {
                $intro[] = 'entre ' . number_format($priceMin, 0, ',', ' ') . ' et ' . number_format($priceMax, 0, ',', ' ') . ' FCFA';
            } elseif ($priceMax !== null) {
                $intro[] = 'jusqu\'à ' . number_format($priceMax, 0, ',', ' ') . ' FCFA';
            } else {
                $intro[] = 'au moins ' . number_format($priceMin, 0, ',', ' ') . ' FCFA';
            }
        }
        if ($storageGb) $intro[] = $storageGb . ' Go';
        if ($ramGb) $intro[] = $ramGb . ' Go RAM';
        if ($brand) $intro[] = ucfirst($brand);
        if ($category === 'phone') $intro[] = 'téléphone';
        if ($category === 'laptop') $intro[] = 'ordinateur portable';
        if ($category === 'tv') $intro[] = 'TV';
        if ($color) $intro[] = ucfirst($color);
        if ($screenInch) $intro[] = $screenInch . '"';
        if ($batteryMah) $intro[] = $batteryMah . ' mAh';
        if ($cameraMp) $intro[] = $cameraMp . ' MP';
        if ($needsDualSim) $intro[] = 'Dual SIM';
        if ($needs5g) $intro[] = '5G';
        if ($refreshHz) $intro[] = $refreshHz . 'Hz';
        if ($hasNfc) $intro[] = 'NFC';
        if ($hasEsim) $intro[] = 'eSIM';
        if ($ipRating) $intro[] = 'IP' . $ipRating;
        if ($selfieMp) $intro[] = $selfieMp . ' MP (selfie)';
        if ($ultraWideMp) $intro[] = $ultraWideMp . ' MP (ultra‑wide)';
        $introText = $intro ? 'Pour ' . implode(', ', $intro) . ', voici des propositions :' : 'Voici des propositions populaires :';

        return $introText;
    }

    private function detectIntent(string $text): array
    {
        $t = mb_strtolower($text);
        // apply coupon
        if (preg_match('/(code\s*promo|coupon)/', $t)) {
            if (preg_match('/([a-z0-9]{3,}-?[a-z0-9]{3,})/i', $text, $m)) {
                return ['apply_coupon', ['code' => strtoupper($m[1])]];
            }
            return ['apply_coupon', []];
        }
        // general QA (livraison, paiement, retours, garanties, contact, vendeur, horaires)
        // Ne pas déclencher QA si c'est une demande de produit (ex: "je veux un téléphone")
        $isProductRequest = preg_match('/\b(je\s*veux|je\s*cherche|montre|affiche|donne|j\'?ai|budget|prix)\s+(un|des|le|la|les|du|de\s+la)?\s*(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire)/i', $t);
        
        if (!$isProductRequest && preg_match('/livraison|delai|frais\s*de\s*livraison|paiement|payer|retour|remboursement|garantie|votre\s*contact|contact\s*(téléphone|telephone|email|whatsapp)|numéro\s*(téléphone|telephone)|appeler|appelle|votre\s*(téléphone|telephone|whatsapp|email)|whatsapp|email|vendeur|devenir\s*vendeur|horaires|ouverture|fermeture/i', $t)) {
            return ['qa', []];
        }
        // track order
        if (preg_match('/(suivre|statut).*commande/i', $t)) {
            if (preg_match('/([A-Z]{3}-\d{8}-[A-Z0-9]+)/', $text, $m)) {
                return ['track_order', ['order_number' => $m[1]]];
            }
            return ['track_order', []];
        }
        // add to cart
        if (preg_match('/(ajoute|mets|mettez) .* (au\s*panier)/i', $t)) {
            return ['add_to_cart', []];
        }
        // default search/recommend
        return ['search', []];
    }

    private function answerGeneralQuestion(string $text): string
    {
        $t = mb_strtolower($text);
        $get = function(string $key, string $fallback = '') {
            try { return \App\Models\Setting::get($key) ?: $fallback; } catch (\Throwable $e) { return $fallback; }
        };
        // Contact
        $phone = $get('contact_phone');
        $whatsapp = $get('contact_whatsapp');
        $email = $get('contact_email');
        $address = $get('contact_address');
        $hours = $get('business_hours');
        // Politiques
        $shipping = $get('shipping_policy', "Livraison disponible dans plusieurs zones. Délai moyen 24–72h.");
        $payment = $get('payment_methods', "Paiement: Mobile Money, carte bancaire (si disponible), et paiement à la livraison selon zone.");
        $returns = $get('return_policy', "Retours possibles sous conditions (produit intact, délai légal).");
        $warranty = $get('warranty_policy', "Garantie fabricant selon produit. Conservez facture/bon de livraison.");
        $sellerUrl = $get('become_seller_url', url('/vendeurs/devenir'));

        if (preg_match('/livraison|delai|frais\s*de\s*livraison/i', $t)) {
            return "Livraison: ".$shipping;
        }
        if (preg_match('/paiement|payer/i', $t)) {
            return "Moyens de paiement: ".$payment;
        }
        if (preg_match('/retour|remboursement/i', $t)) {
            return "Politique de retour: ".$returns;
        }
        if (preg_match('/garantie/i', $t)) {
            return "Garantie: ".$warranty;
        }
        if (preg_match('/vendeur|devenir\s*vendeur/i', $t)) {
            return "Devenir vendeur: rendez‑vous ici: ".$sellerUrl;
        }
        if (preg_match('/horaires|ouverture|fermeture/i', $t)) {
            return $hours ? ("Horaires: ".$hours) : "Nous sommes disponibles 7j/7 en ligne. Pour assistance, contactez‑nous.";
        }
        if (preg_match('/contact|téléphone|telephone|whatsapp|email/i', $t)) {
            $parts = [];
            if ($phone) $parts[] = "Tél: $phone";
            if ($whatsapp) $parts[] = "WhatsApp: $whatsapp";
            if ($email) $parts[] = "Email: $email";
            if ($address) $parts[] = "Adresse: $address";
            return $parts ? ("Contact: ".implode(' | ', $parts)) : "Contact: utilisez le formulaire de contact du site.";
        }
        return "Comment puis‑je vous aider ? Vous pouvez demander des infos sur la livraison, paiement, retours, garantie, contact ou devenir vendeur.";
    }

    private function extractPriceRange(string $text): array
    {
        $t = mb_strtolower($text);
        $num = function ($s) { return (int) str_replace([' ','.',','], '', $s); };
        // entre X et Y
        if (preg_match('/entre\s*(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?\s*(?:et|-|à)\s*(\d+[\s\.,]?\d*)/i', $t, $m)) {
            return [$num($m[1]), $num($m[2])];
        }
        // moins de / au plus / maximum Y
        if (preg_match('/(?:moins\s*de|au\s*plus|max(?:imum)?)\s*(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?/i', $t, $m)) {
            return [null, $num($m[1])];
        }
        // au moins / minimum X
        if (preg_match('/(?:au\s*moins|min(?:imum)?)\s*(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?/i', $t, $m)) {
            return [$num($m[1]), null];
        }
        // un seul nombre avec FCFA interprété comme max
        if (preg_match('/(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)\b/i', $t, $m)) {
            return [null, $num($m[1])];
        }
        return [null, null];
    }

    private function extractNumber(string $text, string $contextRegex): ?int
    {
        if (preg_match('/(\d{1,4})\s?(?:gb|go)\b.*?(?:' . $contextRegex . ')/i', $text, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(?:' . $contextRegex . ').*?(\d{1,4})\s?(?:gb|go)\b/i', $text, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d{1,4})\s?(?:gb|go)\b/i', $text, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    private function extractBrand(string $text): ?string
    {
        // Chercher via la colonne brand la plus fréquente
        $brands = \App\Models\Product::query()
            ->selectRaw('LOWER(brand) as b')
            ->whereNotNull('brand')
            ->groupBy('b')
            ->pluck('b')
            ->filter(fn($b)=>$b!=='' && $b!==null)
            ->take(50) // limiter
            ->toArray();
        foreach ($brands as $b) {
            if ($b && preg_match('/\b' . preg_quote($b, '/') . '\b/i', $text)) {
                return $b;
            }
        }
        // Fallback marques communes + alias
        $common = [
            'samsung','apple','iphone','tecno','infinix','xiaomi','huawei','oppo','nokia','itel',
            'hp','dell','lenovo','asus','toshiba','lg','sony','hisense','realme','vivo','oneplus'
        ];
        $alias = [
            'pixel' => 'google',
            'google pixel' => 'google',
            'galaxy' => 'samsung',
        ];
        // D'abord vérifier les alias
        foreach ($alias as $a => $mapped) {
            if (preg_match('/\b' . preg_quote($a, '/') . '\b/i', $text)) return $mapped;
        }
        foreach ($common as $b) {
            if (preg_match('/\b' . $b . '\b/i', $text)) return $b;
        }
        return null;
    }

    private function extractCategory(string $text): ?string
    {
        $t = mb_strtolower($text);
        if (preg_match('/téléphone|telephone|smartphone/', $t)) return 'phone';
        if (preg_match('/ordinateur|laptop|pc/', $t)) return 'laptop';
        if (preg_match('/tv|télévision|television/', $t)) return 'tv';
        if (preg_match('/frigo|réfrigérateur|refrigerateur/', $t)) return 'fridge';
        if (preg_match('/congélateur|congelateur|freezer/', $t)) return 'freezer';
        if (preg_match('/bouilloire|kettle/', $t)) return 'kettle';
        return null;
    }

    private function resolveCategoryFromDatabase(string $text): array
    {
        try {
            $t = mb_strtolower($text);
            // Charger catégories et sous-catégories actives
            $categories = \App\Models\Category::active()->with('subcategories')->get(['id','name','slug']);
            $bestCat = null; $bestScore = 0; $bestSub = null;
            foreach ($categories as $cat) {
                $names = [mb_strtolower($cat->name), mb_strtolower($cat->slug)];
                $score = 0;
                foreach ($names as $n) { if ($n && str_contains($t, $n)) $score += mb_strlen($n); }
                // Sous-catégories
                $subBest = null; $subScore = 0;
                foreach ($cat->subcategories as $sub) {
                    $sn = [mb_strtolower($sub->name), mb_strtolower($sub->slug)];
                    $s = 0; foreach ($sn as $x) { if ($x && str_contains($t, $x)) $s += mb_strlen($x); }
                    if ($s > $subScore) { $subScore = $s; $subBest = $sub->id; }
                }
                if ($subScore > 0) { $score += $subScore * 1.2; }
                if ($score > $bestScore) { $bestScore = $score; $bestCat = $cat->id; $bestSub = $subBest; }
            }
            if ($bestScore > 0) {
                return [$bestCat, $bestSub];
            }
        } catch (\Throwable $e) {
            // silencieux
        }
        return [null, null];
    }

    private function extractProductKeywords(string $text): array
    {
        $t = mb_strtolower($text);
        $keywords = [];
        $map = [
            'bouilloire' => ['bouilloire','kettle'],
            'congélateur' => ['congélateur','congelateur','freezer','congélateurs','congelateurs'],
        ];
        foreach ($map as $out => $syns) {
            foreach ($syns as $s) {
                // Recherche plus tolérante (avec ou sans boundary pour gérer "les congélateur")
                if (preg_match('/'.preg_quote($s,'/').'/iu', $t)) { $keywords[] = $out; break; }
            }
        }

        // Lexique dynamique depuis la BDD (produits populaires)
        $lex = $this->getDynamicLexicon();
        $tokens = $this->tokenize($t);
        foreach ($tokens as $tok) {
            if (isset($lex['product_tokens'][$tok])) {
                $keywords[] = $tok; // mot produit appris de la base
            }
        }
        return array_slice(array_values(array_unique($keywords)), 0, 6);
    }

    private function areKeywordsRedundantWithCategory(array $keywords, ?string $category): bool
    {
        if ($category === null) return false;
        $map = [
            'kettle' => ['bouilloire','kettle'],
            'freezer' => ['congélateur','congelateur','freezer'],
            'fridge' => ['frigo','réfrigérateur','refrigerateur'],
            'phone' => ['téléphone','telephone','smartphone'],
            'tv' => ['tv','télévision','television'],
            'laptop' => ['ordinateur','laptop','pc']
        ];
        $set = array_map('mb_strtolower', $keywords);
        foreach (($map[$category] ?? []) as $syn) {
            if (in_array(mb_strtolower($syn), $set, true)) return true;
        }
        return false;
    }

    private function getDynamicLexicon(): array
    {
        return Cache::remember('kazar_ai_lexicon', 600, function () {
            $lex = [
                'brands' => [],
                'product_tokens' => [],
            ];
            try {
                // Marques observées
                $brands = Product::query()
                    ->selectRaw('LOWER(brand) as b, COUNT(*) as c')
                    ->whereNotNull('brand')
                    ->groupBy('b')
                    ->orderByDesc('c')
                    ->limit(100)
                    ->pluck('c','b');
                foreach ($brands as $b => $c) { if ($b) $lex['brands'][$b] = (int)$c; }
                // Tokens de titres produits
                $names = Product::query()
                    ->whereNotNull('name')
                    ->orderByDesc('id')
                    ->limit(500)
                    ->pluck('name');
                foreach ($names as $name) {
                    foreach ($this->tokenize(mb_strtolower($name)) as $tok) {
                        if (mb_strlen($tok) < 3) continue;
                        if ($this->isStopword($tok)) continue;
                        $lex['product_tokens'][$tok] = ($lex['product_tokens'][$tok] ?? 0) + 1;
                    }
                }
            } catch (\Throwable $e) {
                // silencieux
            }
            return $lex;
        });
    }

    private function tokenize(string $text): array
    {
        $parts = preg_split('/[^a-z0-9à-ÿ]+/u', $text);
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '' || mb_strlen($p) < 2) continue;
            if ($this->isStopword($p)) continue;
            $out[] = $p;
        }
        return $out;
    }

    private function isStopword(string $w): bool
    {
        static $stop = [
            'le','la','les','un','une','des','de','du','d','et','ou','a','à','au','aux','pour','avec','sans','sur','en','par','mon','ma','mes','ton','ta','tes','son','sa','ses','nos','vos','leurs','je','tu','il','elle','on','nous','vous','ils','elles','ce','cet','cette','ces','plus','moins','très','tres','bon','bien','meilleur','nouveau','neuf','neuve','neufs','neuves','chez','vers','dans','entre','the','and'
        ];
        return in_array($w, $stop, true);
    }

    private function extractColor(string $text): ?string
    {
        $colors = [
            'noir'=>['noir','black'], 'blanc'=>['blanc','white'], 'bleu'=>['bleu','blue'], 'rouge'=>['rouge','red'],
            'or'=>['doré','or','gold'], 'argent'=>['argent','silver'], 'vert'=>['vert','green'], 'violet'=>['violet','purple'],
            'jaune'=>['jaune','yellow'], 'rose'=>['rose','pink']
        ];
        foreach ($colors as $k=>$syns) {
            foreach ($syns as $s) if (preg_match('/\b'.$s.'\b/i', $text)) return $k;
        }
        return null;
    }

    private function colorSynonyms(string $color): array
    {
        $map = [
            'noir'=>['noir','black'], 'blanc'=>['blanc','white'], 'bleu'=>['bleu','blue'], 'rouge'=>['rouge','red'],
            'or'=>['doré','or','gold'], 'argent'=>['argent','silver'], 'vert'=>['vert','green'], 'violet'=>['violet','purple'],
            'jaune'=>['jaune','yellow'], 'rose'=>['rose','pink']
        ];
        return $map[$color] ?? [$color];
    }

    private function extractScreenInch(string $text): ?string
    {
        if (preg_match('/(\d{1,2}(?:[\.,]\d)?)\s*(?:pouces|"|po)\b/i', $text, $m)) {
            return str_replace(',', '.', $m[1]);
        }
        return null;
    }

    private function extractBattery(string $text): ?int
    {
        if (preg_match('/(\d{3,5})\s?m\s?ah/i', $text, $m)) {
            return (int)$m[1];
        }
        return null;
    }

    private function extractCameraMp(string $text): ?int
    {
        if (preg_match('/(\d{1,3})\s?(?:mp|mpx|megapixel)/i', $text, $m)) {
            return (int)$m[1];
        }
        return null;
    }

    private function extractDualSim(string $text): bool
    {
        return preg_match('/dual\s*sim|double\s*sim/i', $text) === 1;
    }

    private function extract5g(string $text): bool
    {
        return preg_match('/\b5\s*g\b/i', $text) === 1;
    }

    private function extractRefreshHz(string $text): ?int
    {
        if (preg_match('/(\d{2,3})\s?hz/i', $text, $m)) return (int)$m[1];
        if (preg_match('/(90|120|144|165)\s?fps/i', $text, $m)) return (int)$m[1];
        return null;
    }

    private function extractNfc(string $text): bool
    {
        return preg_match('/\bnfc\b/i', $text) === 1;
    }

    private function extractEsim(string $text): bool
    {
        return preg_match('/e-?sim/i', $text) === 1;
    }

    private function extractIpRating(string $text): ?int
    {
        if (preg_match('/ip\s?(6[7-9])/i', $text, $m)) return (int)$m[1];
        return null;
    }

    private function extractSelfieMp(string $text): ?int
    {
        if (preg_match('/selfie[^\d]*(\d{1,3})\s?(?:mp|mpx)/i', $text, $m)) return (int)$m[1];
        return null;
    }

    private function extractUltraWideMp(string $text): ?int
    {
        if (preg_match('/(?:ultra\s*wide|grand\s*angle)[^\d]*(\d{1,3})\s?(?:mp|mpx)/i', $text, $m)) return (int)$m[1];
        return null;
    }
}


