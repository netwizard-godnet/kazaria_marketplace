<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\FAQ;
use App\Models\Category;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\ProductView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

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
            
            // Récupérer l'utilisateur connecté ou la session
            $user = Auth::user();
            $userId = $user ? $user->id : null;
            $sessionId = $request->session()->getId();

            // Normaliser le texte pour améliorer la compréhension
            $normalizedText = $this->normalizeText($text);

        // Utiliser le texte normalisé pour l'extraction
        $textForExtraction = $normalizedText;
        
        // Extraction enrichie
        [$priceMin, $priceMax] = $this->extractPriceRange($textForExtraction);
        $storageGb = $this->extractNumber($textForExtraction, '(?:stockage|mémoire\s*interne|rom|go|gb)');
        $ramGb = $this->extractNumber($textForExtraction, '(?:ram|mémoire\s*vive)');
        $brand = $this->extractBrand($textForExtraction);
        $category = $this->extractCategory($textForExtraction); // phone, laptop, tv, fridge, freezer, kettle ...
        // Essayez de résoudre dynamiquement une catégorie/sous-catégorie depuis la BDD
        [$resolvedCategoryId, $resolvedSubcategoryId] = $this->resolveCategoryFromDatabase($textForExtraction);
        $requestedKeywords = $this->extractProductKeywords($textForExtraction); // ex: ['bouilloire']
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
        if (preg_match("/(tu t'appelles comment|ton nom|qui es-tu|comment tu t'appelles|c'est qui|qui es tu|qui êtes-vous)/i", $text)) {
            $responses = [
                "Je suis KAZAR I.A, l'assistant de KAZARIA. Dites-moi votre besoin et je m'occupe du reste !",
                "Je suis KAZAR I.A ! Votre assistant shopping personnel. Comment puis-je vous aider ?",
                "Moi c'est KAZAR I.A ! Je suis là pour vous aider à trouver les meilleurs produits sur KAZARIA.",
                "Je suis KAZAR I.A, votre assistant virtuel. Dites-moi ce que vous cherchez et je vous guide !"
            ];
            $msg = $userName ? ("Ravi de vous revoir, $userName ! ") : '';
            $msg .= $responses[array_rand($responses)];
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_name',
                'intent_params' => [],
                'understood' => []
            ]);
        }
        if (preg_match('/comment ça va|ça va|sa va|comment vas-tu|comment allez-vous/i', $tLower)) {
            $responses = [
                "Ça va très bien, merci ! Je suis là pour vous aider avec vos achats sur KAZARIA.",
                "Très bien, merci ! Comment puis-je vous aider aujourd'hui ?",
                "Parfaitement ! Je suis prêt à vous aider à trouver ce que vous cherchez.",
                "Ça va super ! Dites-moi ce que vous recherchez et je m'en occupe."
            ];
            $msg = $userName ? ("Ça va très bien, merci $userName ! ") : "";
            $msg .= $responses[array_rand($responses)];
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
            $responses = [
                "Avec plaisir ! Besoin d'autre chose ?",
                "De rien ! Je suis là si vous avez d'autres questions.",
                "C'est un plaisir ! N'hésitez pas si vous avez besoin d'aide.",
                "Je vous en prie ! Autre chose ?"
            ];
            $msg = $userName ? ("Avec plaisir, $userName ! ") : "";
            $msg .= $responses[array_rand($responses)];
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_thanks',
                'intent_params' => [],
                'understood' => []
            ]);
        }
        if (preg_match('/au revoir|à bientôt|a bientôt|a plus|à plus|bye|goodbye/i', $tLower)) {
            $responses = [
                "À bientôt ! Bonne journée !",
                "À très bientôt ! Passez une excellente journée !",
                "Au revoir ! N'hésitez pas à revenir si besoin.",
                "À bientôt ! Bon shopping !"
            ];
            $msg = $userName ? ("À bientôt, $userName ! ") : "";
            $msg .= $responses[array_rand($responses)];
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_bye',
                'intent_params' => [],
                'understood' => []
            ]);
        }
        if (preg_match('/\b(bonjour|salut|bonsoir|bonne\s+journée|bonne\s+soirée)\b/i', $tLower)) {
            $greetings = [
                "Bonjour ! Dites-moi votre besoin (budget, catégorie, marque…) et je vous ferai une sélection.",
                "Salut ! Comment puis-je vous aider aujourd'hui ?",
                "Bonjour ! Que recherchez-vous ? Je peux vous aider à trouver le produit idéal.",
                "Bonjour ! Dites-moi ce que vous cherchez et je vous propose les meilleures options."
            ];
            $msg = $userName ? ("Bonjour $userName ! ") : "";
            $msg .= $greetings[array_rand($greetings)];
            return response()->json([
                'success' => true,
                'message' => $msg,
                'items' => [],
                'intent' => 'smalltalk_greeting',
                'intent_params' => [],
                'understood' => []
            ]);
        }

        // Si l'utilisateur salue ou ne donne aucun critère, vérifier d'abord si c'est une question FAQ
        if (!$hasDemand) {
            // Vérifier si c'est une question FAQ simple (un seul mot comme "Contact", "Livraison", etc.)
            $trimmedText = trim(mb_strtolower($text));
            $faqKeywords = ['contact', 'contacter', 'livraison', 'paiement', 'payer', 'retour', 'garantie', 'vendeur', 'horaires'];
            
            if (in_array($trimmedText, $faqKeywords)) {
                // C'est une question FAQ simple, laisser passer pour la détection d'intentions
                // Ne pas retourner greeting ici
            } else {
                // Vérifier si c'est une salutation
                $isGreeting = preg_match('/\b(bonjour|salut|bonsoir|bonne\s+journée|bonne\s+soirée)\b/i', $text);
                if ($isGreeting) {
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
                // Sinon, laisser passer pour la détection d'intentions
            }
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

        // Vérifier d'abord dans la base de connaissances FAQ (AVANT la détection d'intentions)
        // Car les FAQs sont plus spécifiques et doivent avoir priorité
        // MAIS seulement si ce n'est pas clairement une demande de produit
        $isProductSearch = preg_match('/\b(je\s*veux|je\s*cherche|montre|affiche|donne|j\'?ai|budget|prix|combien\s+coûte|combien\s+vaut)\s+(un|des|le|la|les|du|de\s+la)?\s*(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire|produit)/i', $text) ||
                          preg_match('/\b(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire)\s+(128|256|512|64|32)\s*(gb|go)/i', $text) ||
                          preg_match('/\b(montre|montre-moi|affiche|affiche-moi)\s+(des|les|du|de\s+la|de)\s+(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire)/i', $text) ||
                          preg_match('/\b(samsung|apple|iphone|tecno|infinix|xiaomi|huawei|oppo)\s+(téléphone|telephone|smartphone)/i', $text) ||
                          preg_match('/\b(montre|montre-moi|affiche|affiche-moi)\s+(des|les|du|de\s+la|de)\s+(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire)\s+(samsung|apple|iphone|tecno|infinix|xiaomi|huawei|oppo)/i', $text) ||
                          preg_match('/\b(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire)\s+(samsung|apple|iphone|tecno|infinix|xiaomi|huawei|oppo)/i', $text) ||
                          // Détecter les recherches avec couleur ou caractéristiques
                          preg_match('/\b(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire)\s+(noir|blanc|bleu|rouge|or|argent|vert|violet|jaune|rose|gris|marron|beige)/i', $text) ||
                          preg_match('/\b(noir|blanc|bleu|rouge|or|argent|vert|violet|jaune|rose|gris|marron|beige)\s+(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire)/i', $text);
        
        if (!$isProductSearch) {
            $faqMatch = FAQ::findMatching($normalizedText);
            if (!$faqMatch) {
                // Essayer aussi avec le texte original (au cas où la normalisation aurait supprimé des infos importantes)
                $faqMatch = FAQ::findMatching($text);
            }
            if ($faqMatch) {
                return response()->json([
                    'success' => true,
                    'message' => $faqMatch->answer,
                    'items' => [],
                    'intent' => 'faq',
                    'intent_params' => ['faq_id' => $faqMatch->id],
                    'understood' => []
                ]);
            }
        }
        
        // Détecter les intentions spéciales (catégories, promotions) APRÈS la FAQ
        // Car ces intentions sont moins spécifiques que les FAQs
        [$intent, $intentParams] = $this->detectIntent($text, $normalizedText);
        
        // Si c'est une recherche de produit, forcer l'intention à 'search'
        if ($isProductSearch && !in_array($intent, ['favorites_info', 'category_info', 'promotion_info', 'review_info', 'product_info'])) {
            $intent = 'search';
        }

        // Intent déjà détecté plus haut pour les intentions spéciales
        // Si pas encore détecté, le détecter maintenant
        if (!isset($intent)) {
            [$intent, $intentParams] = $this->detectIntent($text, $normalizedText);
        }

        // Questions sur les favoris
        if ($intent === 'favorites_info') {
            try {
                $favoritesInfo = $this->answerFavoritesQuestion($text, $userId, $sessionId);
                if ($favoritesInfo) {
                    return response()->json([
                        'success' => true,
                        'message' => $favoritesInfo['message'],
                        'items' => $favoritesInfo['items'],
                        'intent' => 'favorites_info',
                        'intent_params' => [],
                        'understood' => []
                    ]);
                }
            } catch (\Exception $e) {
                // En cas d'erreur, retourner un message d'erreur gracieux
                return response()->json([
                    'success' => true,
                    'message' => "Je n'ai pas pu récupérer vos favoris pour le moment. Veuillez réessayer plus tard.",
                    'items' => [],
                    'intent' => 'favorites_info',
                    'intent_params' => [],
                    'understood' => []
                ]);
            }
        }

        // Questions sur un produit spécifique
        if ($intent === 'product_info') {
            $productInfo = $this->answerProductQuestion($text, $intentParams);
            if ($productInfo) {
                return response()->json([
                    'success' => true,
                    'message' => $productInfo['message'],
                    'items' => $productInfo['items'] ?? [],
                    'intent' => 'product_info',
                    'intent_params' => $intentParams,
                    'understood' => []
                ]);
            }
        }

        // Questions sur les catégories
        if ($intent === 'category_info') {
            $categoryInfo = $this->answerCategoryQuestion($text);
            if ($categoryInfo) {
                return response()->json([
                    'success' => true,
                    'message' => $categoryInfo,
                    'items' => [],
                    'intent' => 'category_info',
                    'intent_params' => [],
                    'understood' => []
                ]);
            }
        }

        // Questions sur les avis/notes
        if ($intent === 'review_info') {
            $reviewInfo = $this->answerReviewQuestion($text, $intentParams);
            if ($reviewInfo) {
                return response()->json([
                    'success' => true,
                    'message' => $reviewInfo,
                    'items' => [],
                    'intent' => 'review_info',
                    'intent_params' => $intentParams,
                    'understood' => []
                ]);
            }
        }

        // Questions sur les promotions
        if ($intent === 'promotion_info') {
            $promoInfo = $this->answerPromotionQuestion($text);
            if ($promoInfo) {
                return response()->json([
                    'success' => true,
                    'message' => $promoInfo['message'],
                    'items' => $promoInfo['items'] ?? [],
                    'intent' => 'promotion_info',
                    'intent_params' => [],
                    'understood' => []
                ]);
            }
        }

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

        $products = $q->limit(10)->get(['id','name','slug','image','images','price','old_price','discount_percentage','brand']);
        
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
            $products = $q2->limit(10)->get(['id','name','slug','image','images','price','old_price','discount_percentage','brand']);
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
            $products = $q3a->limit(10)->get(['id','name','slug','image','images','price','old_price','discount_percentage','brand']);
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
            $products = $q3->limit(10)->get(['id','name','slug','image','images','price','old_price','discount_percentage','brand']);
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

        // Si l'intention est "search" mais qu'on n'a pas de demande claire, essayer le fallback
        if ($intent === 'search' && !$hasDemand) {
            $fallback = $this->tryFallbackInterpretations($text, $normalizedText);
            if ($fallback) {
                return response()->json($fallback);
            }
        }

        $reply = $this->buildReply($products, $priceMin, $priceMax, $storageGb, $ramGb, $brand, $category, $color, $screenInch, $batteryMah, $cameraMp, $needsDualSim, $needs5g, $refreshHz, $hasNfc, $hasEsim, $ipRating, $selfieMp, $ultraWideMp, $requestedBrandKeyword, $brandFound);

        // Transformer les images en URLs complètes juste avant de renvoyer
        $productsArray = $products->map(function($product) {
            $productArray = $product->toArray();
            // Construire l'URL de l'image - utiliser les données déjà chargées
            $imageUrl = asset('images/produit.jpg'); // Par défaut
            
            // Priorité 1: images (array)
            if (!empty($productArray['images']) && is_array($productArray['images']) && count($productArray['images']) > 0) {
                $firstImg = $productArray['images'][0];
                if (filter_var($firstImg, FILTER_VALIDATE_URL)) {
                    $imageUrl = $firstImg;
                } elseif (strpos($firstImg, 'products/') === 0) {
                    $imageUrl = asset('storage/' . $firstImg);
                } elseif (strpos($firstImg, 'images/') === 0) {
                    $imageUrl = asset($firstImg);
                } else {
                    $imageUrl = asset('storage/' . $firstImg);
                }
            }
            // Priorité 2: image (string)
            elseif (!empty($productArray['image'])) {
                $img = $productArray['image'];
                if (filter_var($img, FILTER_VALIDATE_URL)) {
                    $imageUrl = $img;
                } elseif (strpos($img, 'storage/') === 0) {
                    $imageUrl = asset($img);
                } elseif (strpos($img, 'products/') === 0) {
                    $imageUrl = asset('storage/' . $img);
                } elseif (strpos($img, 'images/') === 0) {
                    $imageUrl = asset($img);
                } else {
                    $imageUrl = asset('storage/' . $img);
                }
            }
            
            $productArray['image'] = $imageUrl;
            return $productArray;
        });

        return response()->json([
            'success' => true,
            'message' => $reply,
            'items' => $productsArray,
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
        $userName = session('ai_user_name');
        
        if ($products->isEmpty()) {
            $noResultsResponses = [
                "Je n'ai pas trouvé d'article correspondant exactement à vos critères.",
                "Désolé, je n'ai pas trouvé de produit correspondant à votre recherche.",
                "Aucun produit ne correspond exactement à vos critères pour le moment."
            ];
            $msg = $noResultsResponses[array_rand($noResultsResponses)];
            if ($requestedBrandKeyword) {
                $displayBrand = ($requestedBrandKeyword === 'pixel') ? 'Pixel' : ucfirst($requestedBrandKeyword);
                $msg .= " Nous n'avons pas de " . $displayBrand . " pour le moment.";
            }
            $suggestions = [
                "Souhaitez-vous élargir le budget ou voir d'autres marques similaires ?",
                "Voulez-vous que je vous propose des alternatives ou ajuster vos critères ?",
                "Je peux vous proposer des produits similaires si vous le souhaitez."
            ];
            $msg .= " " . $suggestions[array_rand($suggestions)];
            return $msg;
        }
        
        // Si une marque était demandée mais pas trouvée dans les résultats
        if ($requestedBrandKeyword && !$brandFound) {
            $displayBrand = ($requestedBrandKeyword === 'pixel') ? 'Pixel' : ucfirst($requestedBrandKeyword);
            $alternativesResponses = [
                "Je n'ai pas trouvé de " . $displayBrand . " correspondant à vos critères. Voici des alternatives similaires :",
                "Aucun " . $displayBrand . " ne correspond à vos critères. Mais j'ai trouvé ces alternatives intéressantes :",
                "Pas de " . $displayBrand . " pour le moment avec ces critères. Voici d'autres options qui pourraient vous plaire :"
            ];
            return $alternativesResponses[array_rand($alternativesResponses)];
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
        
        // Varier les introductions selon le contexte
        if ($intro) {
            $introVariations = [
                'Pour ' . implode(', ', $intro) . ', voici des propositions :',
                'Voici ce que j\'ai trouvé pour ' . implode(', ', $intro) . ' :',
                'J\'ai sélectionné pour vous ces produits correspondant à ' . implode(', ', $intro) . ' :',
                'Parfait ! Voici des produits qui correspondent à ' . implode(', ', $intro) . ' :'
            ];
            $introText = $introVariations[array_rand($introVariations)];
        } else {
            $popularVariations = [
                'Voici des propositions populaires :',
                'Voici quelques produits qui pourraient vous intéresser :',
                'J\'ai sélectionné pour vous ces produits populaires :',
                'Voici une sélection de produits qui ont du succès :'
            ];
            $introText = $popularVariations[array_rand($popularVariations)];
        }
        
        // Ajouter une touche personnelle si on connaît le nom
        if ($userName && rand(0, 2) === 0) {
            $introText = $userName . ', ' . mb_strtolower($introText);
        }

        return $introText;
    }

    /**
     * Normaliser le texte pour améliorer la compréhension
     */
    private function normalizeText(string $text): string
    {
        // Convertir en minuscules
        $normalized = mb_strtolower($text);
        
        // Remplacer les synonymes et variations courantes
        $synonyms = [
            // Questions de prix
            'combien coûte' => 'prix',
            'combien vaut' => 'prix',
            'quel est le prix' => 'prix',
            'quelle est la prix' => 'prix',
            'quelle est le prix' => 'prix',
            'c\'est combien' => 'prix',
            'c est combien' => 'prix',
            'ça coûte combien' => 'prix',
            'ca coute combien' => 'prix',
            'prix de' => 'prix',
            'coût de' => 'prix',
            'tarif de' => 'prix',
            'valeur de' => 'prix',
            
            // Caractéristiques
            'caractéristiques' => 'caracteristiques',
            'spécifications' => 'specifications',
            'spécificités' => 'specifications',
            'infos' => 'informations',
            'détails' => 'details',
            'fiche technique' => 'specifications',
            'fiche technique' => 'specifications',
            'données techniques' => 'specifications',
            
            // Disponibilité
            'disponible' => 'stock',
            'en stock' => 'stock',
            'disponibilité' => 'stock',
            'il y a' => 'stock',
            'y a t il' => 'stock',
            'y a-t-il' => 'stock',
            'avez-vous' => 'stock',
            'avez vous' => 'stock',
            'est disponible' => 'stock',
            'sont disponibles' => 'stock',
            
            // Avis
            'avis' => 'avis',
            'commentaires' => 'avis',
            'notes' => 'avis',
            'évaluations' => 'avis',
            'evaluations' => 'avis',
            'opinions' => 'avis',
            'que pensent' => 'avis',
            'que pense' => 'avis',
            'ce que pensent' => 'avis',
            'ce que pense' => 'avis',
            'témoignages' => 'avis',
            'temoignages' => 'avis',
            
            // Promotions
            'promo' => 'promotion',
            'réduction' => 'promotion',
            'reduction' => 'promotion',
            'remise' => 'promotion',
            'offre' => 'promotion',
            'solde' => 'promotion',
            'promotions' => 'promotion',
            'promos' => 'promotion',
            'réductions' => 'promotion',
            'reductions' => 'promotion',
            'remises' => 'promotion',
            'offres' => 'promotion',
            'soldes' => 'promotion',
            'en promotion' => 'promotion',
            'en promo' => 'promotion',
            
            // Catégories
            'types' => 'categories',
            'sortes' => 'categories',
            'genres' => 'categories',
            'gammes' => 'categories',
            'familles' => 'categories',
            
            // Recherche
            'je cherche' => 'recherche',
            'je veux' => 'recherche',
            'j\'ai besoin' => 'recherche',
            'j ai besoin' => 'recherche',
            'montre-moi' => 'recherche',
            'montre moi' => 'recherche',
            'affiche' => 'recherche',
            'donne-moi' => 'recherche',
            'donne moi' => 'recherche',
        ];
        
        foreach ($synonyms as $synonym => $replacement) {
            $normalized = str_ireplace($synonym, $replacement, $normalized);
        }
        
        // Normaliser les espaces multiples
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        // Supprimer la ponctuation excessive (garder les ? et !)
        $normalized = preg_replace('/[.,;:]+/', ' ', $normalized);
        
        return trim($normalized);
    }

    private function detectIntent(string $text, string $normalizedText = null): array
    {
        // Utiliser le texte normalisé si fourni, sinon normaliser
        // MAIS pour les patterns, utiliser le texte original en minuscules pour éviter les problèmes de normalisation
        $t = mb_strtolower($text);
        
        // apply coupon
        if (preg_match('/(code\s*promo|coupon)/', $t)) {
            if (preg_match('/([a-z0-9]{3,}-?[a-z0-9]{3,})/i', $text, $m)) {
                return ['apply_coupon', ['code' => strtoupper($m[1])]];
            }
            return ['apply_coupon', []];
        }
        
        // Questions sur les favoris - DOIT être AVANT les autres détections
        $favoritePatterns = [
            '/\b(mes\s+)?favoris\b/i',
            '/\b(mes\s+)?produits\s+favoris/i',
            '/\b(mes\s+)?produits\s+aimés/i',
            '/\b(mes\s+)?produits\s+enregistrés/i',
            '/\b(mes\s+)?souhaits/i',
            '/\b(mes\s+)?wishlist/i',
            '/\b(montre|affiche|donne)\s+(moi\s+)?(mes\s+)?favoris/i',
            '/\b(quels|quelles)\s+(sont|est)\s+(mes\s+)?favoris/i',
            '/\b(liste|liste\s+des)\s+(mes\s+)?favoris/i',
        ];
        
        foreach ($favoritePatterns as $pattern) {
            if (preg_match($pattern, $t)) {
                return ['favorites_info', []];
            }
        }
        
        // Questions sur les catégories - DOIT être AVANT product_info
        // Utiliser le texte original pour éviter les problèmes d'encodage
        $tOriginal = mb_strtolower($text);
        $categoryPatterns = [
            '/\b(quelles|quels)\s+(categories|categorie|catégories|catégorie|types|sortes|genres)\s+(avez|avez-vous|avez\s+vous|proposez|proposez-vous|disposez|disposez-vous)/i',
            '/\b(quelles|quels)\s+(categories|categorie|catégories|catégorie|types|sortes|genres)/i', // Pattern simplifié - correspond même avec d'autres mots après
            '/\b(quels|quelles)\s+(types|sortes|genres)\s+(de\s+)?(produits?)/i',
            '/\b(qu\s+avez\s+vous|qu\'avez-vous|avez\s+vous|qu\'est-ce\s+que\s+vous\s+avez)\s+(comme\s+)?(categories|categorie|catégories|catégorie|types|produits?)/i',
            '/\b(liste|liste\s+des)\s+(categories|categorie|catégories|catégorie|types|produits?)/i',
            '/\b(categories|categorie|catégories|catégorie|types|sortes|genres)\s+(disponibles?|proposées?)/i',
        ];
        
        foreach ($categoryPatterns as $pattern) {
            if (preg_match($pattern, $tOriginal) || preg_match($pattern, $t)) {
                return ['category_info', []];
            }
        }
        
        // Questions sur les promotions - DOIT être AVANT product_info
        $promotionPatterns = [
            '/\b(quelles|quels)\s+(sont|avez-vous|avez\s+vous|y\s+a\s+t\s+il|y\s+a-t-il)\s+(les\s+)?(promotions?|promo|offres?|reductions?|remises?)\s+(en\s+)?(cours|actuelles?|disponibles?)/i',
            '/\b(promotions?|promo|reduction|reductions?|remise|remises?|offre|offres?|solde|soldes?)\s+(en\s+)?(cours|actuelles?|actuelles?|disponibles?|disponible)/i',
            '/\b(quelles|quels)\s+(sont|avez-vous|avez\s+vous|y\s+a\s+t\s+il|y\s+a-t-il)\s+(les\s+)?(promotions?|promo|offres?|reductions?|remises?)\b/i',
            '/\b(il\s+y\s+a|y\s+a\s+t\s+il|y\s+a-t-il)\s+(des\s+)?(promotions?|promo|offres?|reductions?)/i',
            '/\b(quels|quelles)\s+(sont|est)\s+(vos|vos\s+meilleures?)\s+(promotions?|promo|offres?)/i',
        ];
        
        foreach ($promotionPatterns as $pattern) {
            if (preg_match($pattern, $t)) {
                return ['promotion_info', []];
            }
        }
        
        // Questions sur un produit spécifique - Patterns très flexibles
        $productInfoPatterns = [
            // "Quel est le prix du X?", "Combien coûte le X?", "Le prix du X?"
            '/\b(prix|cout|tarif|valeur)\s+(du|de\s+la|de|le|la|les|un|une|d\s+un|d\s+une)\s+/i',
            '/\b(combien\s+)?(coute|coûte|vaut)\s+(le|la|les|un|une|du|de\s+la|de)\s+/i',
            '/\b(quel|quelle|quels|quelles)\s+(est|sont|a|ont)\s+(le|la|les|un|une)\s+(prix|cout|tarif)\s+(du|de\s+la|de|le|la|les)\s+/i',
            
            // "Caractéristiques du X", "Infos sur le X", "Détails du X"
            '/\b(caracteristiques|specifications|specs|details|infos|informations|description)\s+(du|de\s+la|de|le|la|les|sur|concernant)\s+/i',
            '/\b(quel|quelle|quels|quelles)\s+(sont|est)\s+(les|la)\s+(caracteristiques|specifications|details|infos)\s+(du|de\s+la|de|le|la|les)\s+/i',
            
            // "Le stock du X?", "Disponibilité du X?"
            '/\b(stock|disponibilite|disponible)\s+(du|de\s+la|de|le|la|les|d\s+un|d\s+une)\s+/i',
            '/\b(il\s+y\s+a|y\s+a\s+t\s+il|y\s+a-t-il)\s+(du|de\s+la|de|le|la|les)\s+/i',
            '/\b(est|sont)\s+(il|elle|ils|elles)\s+(disponible|en\s+stock)\s+(le|la|les|du|de\s+la|de)\s+/i',
            
            // "La garantie du X?"
            '/\b(garantie|warranty)\s+(du|de\s+la|de|le|la|les)\s+/i',
            
            // "Quel est le X?", "C'est quoi le X?" - MAIS exclure les questions sur catégories/promotions
            '/\b(quel|quelle|quels|quelles)\s+(est|sont|a|ont)\s+(le|la|les|un|une)\s+(?!categories|categorie|promotions?|promo)/i',
            '/\b(c\s+est\s+quoi|qu\s+est\s+ce\s+que|qu\'est-ce que)\s+(le|la|les|un|une|du|de\s+la|de)\s+(?!categories|categorie|promotions?|promo)/i',
        ];
        
        foreach ($productInfoPatterns as $pattern) {
            if (preg_match($pattern, $t)) {
                $productName = $this->extractProductNameFromQuestion($text, $t);
                if ($productName) {
                    return ['product_info', ['product_name' => $productName]];
                }
            }
        }
        
        // Questions sur les avis/notes - Patterns flexibles
        $reviewPatterns = [
            '/\b(avis|commentaires|notes|note|evaluation|evaluations|opinions|opinion)\s+(sur|du|de\s+la|de|le|la|les|concernant)/i',
            '/\b(comment|que\s+pensent|que\s+pense|pensent|pense)\s+(les\s+)?(clients?|utilisateurs?|gens|acheteurs?)/i',
            '/\b(quels|quelles)\s+(sont|est)\s+(les|la)\s+(avis|commentaires|notes|opinions)\s+(sur|du|de\s+la|de|le|la|les)/i',
            '/\b(est|sont)\s+(il|elle|ils|elles)\s+(bien|bon|mauvais|mauvaises)\s+(le|la|les|du|de\s+la|de)\s+/i',
        ];
        
        foreach ($reviewPatterns as $pattern) {
            if (preg_match($pattern, $t)) {
                $productName = $this->extractProductNameFromQuestion($text, $t);
                return ['review_info', ['product_name' => $productName]];
            }
        }
        
        
        // general QA (livraison, paiement, retours, garanties, contact, vendeur, horaires)
        // Ne pas déclencher QA si c'est une demande de produit (ex: "je veux un téléphone")
        $isProductRequest = preg_match('/\b(je\s*veux|je\s*cherche|montre|affiche|donne|j\'?ai|budget|prix)\s+(un|des|le|la|les|du|de\s+la)?\s*(téléphone|telephone|smartphone|laptop|ordinateur|tv|frigo|réfrigérateur|congélateur|bouilloire)/i', $t);
        
        // Mots-clés simples qui déclenchent directement QA (doivent être seuls ou avec peu de contexte)
        $simpleKeywords = ['contact', 'contacter', 'téléphone', 'telephone', 'whatsapp', 'email', 'livraison', 'paiement', 'payer', 'retour', 'garantie', 'vendeur', 'horaires', 'ouverture', 'fermeture'];
        $trimmedText = trim($t);
        if (!$isProductRequest && (in_array($trimmedText, $simpleKeywords) || in_array($trimmedText, array_map('mb_strtolower', $simpleKeywords)))) {
            return ['qa', []];
        }
        
        // Patterns améliorés pour détecter les questions FAQ
        $qaPatterns = [
            '/\b(comment|comment\s+puis|comment\s+faire|comment\s+on)\s+(suivre|suis|suis-je|suivre\s+ma|suivre\s+mon)\s+(commande|colis)/i',
            '/\b(quels?|quelles?)\s+(sont|est)\s+(les?\s+)?(moyens?\s+de\s+)?paiement/i',
            '/\b(quels?|quelles?)\s+(sont|est)\s+(les?\s+)?(délais?|delais?)\s+(de\s+)?livraison/i',
            '/\b(quels?|quelles?)\s+(sont|est)\s+(les?\s+)?frais\s+(de\s+)?livraison/i',
            '/\b(puis|peut|peux)\s*(-je\s+)?(retourner|retour|échanger|rembourser)/i',
            '/\b(quelle|quel)\s+(est|sont)\s+(la\s+)?garantie/i',
            '/\b(comment|comment\s+puis)\s+(vous\s+)?contacter/i',
            '/\b(comment|comment\s+puis)\s+(devenir|être)\s+vendeur/i',
            '/\b(contact|contacter|téléphone|telephone|whatsapp|email|numéro)/i',
            '/\b(livraison|delai|delais?|expédition|expedition|frais\s+livraison)/i',
            '/\b(paiement|payer|moyen\s+de\s+paiement)/i',
            '/\b(retour|remboursement|échanger|echanger)/i',
            '/\b(garantie|warranty)/i',
            '/\b(vendeur|devenir\s+vendeur|vendre)/i',
            '/\b(horaires?|ouverture|fermeture)/i',
        ];
        
        if (!$isProductRequest) {
            foreach ($qaPatterns as $pattern) {
                if (preg_match($pattern, $t)) {
                    return ['qa', []];
                }
            }
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
        
        // moins de / au plus / maximum Y / X ou moins / X et moins
        if (preg_match('/(?:moins\s*de|au\s*plus|max(?:imum)?)\s*(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?/i', $t, $m)) {
            return [null, $num($m[1])];
        }
        // Pattern "X FCFA ou moins" / "X F ou moins" / "X.000F ou moins"
        if (preg_match('/(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?\s*(?:ou|et)\s*(?:moins|inférieur|inférieure)/i', $t, $m)) {
            return [null, $num($m[1])];
        }
        // Pattern "inférieur à X" / "inférieure à X"
        if (preg_match('/(?:inférieur(?:e)?\s*(?:à|a))\s*(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?/i', $t, $m)) {
            return [null, $num($m[1])];
        }
        
        // au moins / minimum X / X ou plus / X et plus
        if (preg_match('/(?:au\s*moins|min(?:imum)?)\s*(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?/i', $t, $m)) {
            return [$num($m[1]), null];
        }
        // Pattern "X FCFA ou plus" / "X F ou plus" / "X.000F ou plus"
        if (preg_match('/(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?\s*(?:ou|et)\s*(?:plus|supérieur|supérieure)/i', $t, $m)) {
            return [$num($m[1]), null];
        }
        // Pattern "plus de X" / "plus que X" / "supérieur à X"
        if (preg_match('/(?:plus\s*(?:de|que)|supérieur(?:e)?\s*(?:à|a))\s*(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)?/i', $t, $m)) {
            return [$num($m[1]), null];
        }
        
        // un seul nombre avec FCFA interprété comme max (seulement si pas de contexte "ou plus")
        if (!preg_match('/ou\s*plus|et\s*plus|supérieur/i', $t)) {
            if (preg_match('/(\d+[\s\.,]?\d*)\s*(?:fcfa|fr|f)\b/i', $t, $m)) {
                return [null, $num($m[1])];
            }
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
            'samsung galaxy' => 'samsung',
            'iphone' => 'apple',
            'ipad' => 'apple',
            'macbook' => 'apple',
            'mac' => 'apple',
            'redmi' => 'xiaomi',
            'mi' => 'xiaomi',
            'xiaomi mi' => 'xiaomi',
            'note' => 'samsung', // Samsung Galaxy Note
            'galaxy note' => 'samsung',
            'galaxy s' => 'samsung',
            'galaxy a' => 'samsung',
            'thinkpad' => 'lenovo',
            'ideapad' => 'lenovo',
            'yoga' => 'lenovo',
            'inspiron' => 'dell',
            'xps' => 'dell',
            'pavilion' => 'hp',
            'envy' => 'hp',
            'vivobook' => 'asus',
            'zenbook' => 'asus',
            'rog' => 'asus',
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
        // Téléphones - patterns plus complets
        if (preg_match('/téléphone|telephone|smartphone|mobile|portable|gsm|cellulaire|iphone|galaxy|redmi|mi\s+\d+/i', $t)) return 'phone';
        
        // Ordinateurs - patterns plus complets
        if (preg_match('/ordinateur|laptop|pc|portable|notebook|macbook|thinkpad|ideapad|pavilion|inspiron|xps|vivobook|zenbook/i', $t)) return 'laptop';
        
        // TV - patterns plus complets
        if (preg_match('/tv|télévision|television|téléviseur|televiseur|écran\s+tv|smart\s+tv/i', $t)) return 'tv';
        
        // Réfrigérateurs - patterns plus complets
        if (preg_match('/frigo|réfrigérateur|refrigerateur|refrigerateur|frigidaire/i', $t)) return 'fridge';
        
        // Congélateurs - patterns plus complets
        if (preg_match('/congélateur|congelateur|freezer|congel/i', $t)) return 'freezer';
        
        // Bouilloires - patterns plus complets
        if (preg_match('/bouilloire|kettle|bouilloir/i', $t)) return 'kettle';
        
        // Tablettes
        if (preg_match('/tablette|ipad|tab\s+\d+|matepad/i', $t)) return 'tablet';
        
        // Écouteurs/Casques
        if (preg_match('/écouteur|ecouteur|casque|headphone|earbud|airpods/i', $t)) return 'headphone';
        
        // Montres connectées
        if (preg_match('/montre\s+connectée|smartwatch|watch|montre\s+intelligente/i', $t)) return 'smartwatch';
        
        return null;
    }

    private function resolveCategoryFromDatabase(string $text): array
    {
        try {
            $t = mb_strtolower($text);
            // Charger catégories et sous-catégories actives
            $categories = \App\Models\Category::active()
                ->with(['subcategories' => function($query) {
                    $query->where('is_active', true)->orderBy('order')->orderBy('name');
                }])
                ->get(['id','name','slug']);
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
            'le','la','les','un','une','des','de','du','d','et','ou','a','à','au','aux','pour','avec','sans','sur','en','par','mon','ma','mes','ton','ta','tes','son','sa','ses','nos','vos','leurs','je','tu','il','elle','on','nous','vous','ils','elles','ce','cet','cette','ces','plus','moins','très','tres','bon','bien','meilleur','nouveau','neuf','neuve','neufs','neuves','chez','vers','dans','entre','the','and','est','sont','a','ont','être','avoir','faire','aller','venir','voir','dire','savoir','vouloir','pouvoir','devoir','falloir','donner','prendre','mettre','faire','trouver','chercher','acheter','vendre'
        ];
        return in_array($w, $stop, true);
    }

    private function extractColor(string $text): ?string
    {
        $colors = [
            'noir'=>['noir','black','noire','noirs','noires'],
            'blanc'=>['blanc','white','blanche','blancs','blanches'],
            'bleu'=>['bleu','blue','bleue','bleus','bleues'],
            'rouge'=>['rouge','red','rouges'],
            'or'=>['doré','or','gold','dorée','dorés','dorées','golden'],
            'argent'=>['argent','silver','argente','argentes','silver'],
            'vert'=>['vert','green','verte','verts','vertes'],
            'violet'=>['violet','purple','violette','violets','violettes'],
            'jaune'=>['jaune','yellow','jaunes'],
            'rose'=>['rose','pink','roses'],
            'gris'=>['gris','gray','grey','grise','grises'],
            'marron'=>['marron','brown','brun','brune','bruns','brunes'],
            'beige'=>['beige','beiges'],
            'turquoise'=>['turquoise','turquoises'],
            'cyan'=>['cyan','cyans'],
            'magenta'=>['magenta','magentas'],
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

    /**
     * Extraire le nom d'un produit depuis une question avec correspondance floue
     */
    private function extractProductNameFromQuestion(string $text, string $normalizedText = null): ?string
    {
        $t = $normalizedText ?: mb_strtolower($text);
        
        // Patterns pour extraire le nom du produit
        $patterns = [
            // "du iPhone 15", "de Samsung Galaxy", "le Samsung S23"
            '/\b(du|de\s+la|de|le|la|les|un|une|d\s+un|d\s+une)\s+([a-z0-9\s\-]+?)(?:\s|$|,|\?|\.|combien|prix|cout|caracteristiques|stock|disponible|garantie|avis)/i',
            // "iPhone 15", "Samsung Galaxy" (sans article)
            '/\b([A-Z][a-z0-9]+(?:\s+[A-Z]?[a-z0-9]+)*)\s+(?:combien|prix|cout|caracteristiques|stock|disponible|garantie|avis)/i',
            // "sur le X", "concernant le X"
            '/\b(sur|concernant|a\s+propos\s+de)\s+(le|la|les|du|de\s+la|de|un|une)\s+([a-z0-9\s\-]+?)(?:\s|$|,|\?|\.)/i',
        ];
        
        $candidates = [];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $name = trim($m[count($m) - 1]); // Dernier groupe capturé
                if (strlen($name) >= 3) {
                    $candidates[] = $name;
                }
            }
        }
        
        // Si aucun pattern ne correspond, essayer d'extraire les mots significatifs
        if (empty($candidates)) {
            // Chercher des mots qui ressemblent à des noms de produits (majuscules, chiffres, etc.)
            if (preg_match_all('/\b([A-Z][a-z0-9]+(?:\s+[A-Z]?[a-z0-9]+)*|\d+\s*[A-Z][a-z]+)/', $text, $matches)) {
                $candidates = $matches[1];
            }
        }
        
        // Filtrer et nettoyer les candidats
        $stopwords = ['les', 'des', 'du', 'de', 'la', 'le', 'un', 'une', 'est', 'sont', 'a', 'ont', 
                      'quel', 'quelle', 'quels', 'quelles', 'combien', 'prix', 'cout', 'tarif',
                      'caracteristiques', 'specifications', 'details', 'infos', 'informations',
                      'stock', 'disponible', 'disponibilite', 'garantie', 'avis', 'commentaires'];
        
        foreach ($candidates as $candidate) {
            $words = preg_split('/\s+/', mb_strtolower($candidate));
            $filtered = array_filter($words, function($w) use ($stopwords) {
                return !in_array($w, $stopwords) && strlen($w) >= 2;
            });
            
            if (!empty($filtered) && count($filtered) >= 1) {
                $productName = implode(' ', array_slice($filtered, 0, 5));
                
                // Vérifier si ce nom correspond à un produit dans la base
                $found = $this->fuzzyFindProduct($productName);
                if ($found) {
                    return $found;
                }
            }
        }
        
        // Retourner le meilleur candidat même sans correspondance exacte
        if (!empty($candidates)) {
            $best = $candidates[0];
            $words = preg_split('/\s+/', mb_strtolower($best));
            $filtered = array_filter($words, function($w) use ($stopwords) {
                return !in_array($w, $stopwords) && strlen($w) >= 2;
            });
            if (!empty($filtered)) {
                return implode(' ', array_slice($filtered, 0, 5));
            }
        }
        
        return null;
    }

    /**
     * Recherche floue d'un produit dans la base de données
     */
    private function fuzzyFindProduct(string $searchTerm): ?string
    {
        if (strlen($searchTerm) < 3) {
            return null;
        }
        
        // Recherche exacte d'abord
        $exact = Product::active()
            ->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('model', 'like', '%' . $searchTerm . '%');
            })
            ->first();
        
        if ($exact) {
            return $searchTerm;
        }
        
        // Recherche par mots individuels
        $words = explode(' ', $searchTerm);
        if (count($words) > 1) {
            // Essayer avec chaque mot significatif
            foreach ($words as $word) {
                if (strlen($word) >= 3) {
                    $found = Product::active()
                        ->where(function($q) use ($word) {
                            $q->where('name', 'like', '%' . $word . '%')
                              ->orWhere('brand', 'like', '%' . $word . '%');
                        })
                        ->first();
                    
                    if ($found) {
                        // Retourner le nom de marque ou le début du nom du produit
                        return $found->brand ?: substr($found->name, 0, 30);
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Répondre aux questions sur un produit spécifique
     */
    private function answerProductQuestion(string $text, array $params): ?array
    {
        $productName = $params['product_name'] ?? null;
        if (!$productName) {
            return null;
        }

        $t = mb_strtolower($text);
        
        // Recherche floue du produit avec plusieurs stratégies
        $product = $this->findProductFuzzy($productName);

        if (!$product) {
            $userName = session('ai_user_name');
            $notFoundMessages = [
                "Je n'ai pas trouvé de produit correspondant à \"$productName\". Pouvez-vous être plus précis ?",
                "Désolé, je n'ai pas trouvé de produit nommé \"$productName\". Pourriez-vous vérifier l'orthographe ou être plus spécifique ?",
                "Aucun produit ne correspond à \"$productName\". Essayez avec le nom complet ou la marque.",
                "Je ne trouve pas de produit correspondant à \"$productName\". Pouvez-vous donner plus de détails ?"
            ];
            $msg = ($userName ? "$userName, " : "") . $notFoundMessages[array_rand($notFoundMessages)];
            return [
                'message' => $msg,
                'items' => []
            ];
        }

        $userName = session('ai_user_name');
        $introMessages = [
            "Voici les informations sur " . $product->name . " :\n\n",
            "Parfait ! Voici ce que j'ai trouvé sur " . $product->name . " :\n\n",
            "J'ai trouvé " . $product->name . ". Voici les détails :\n\n",
            "Voici tout ce que je sais sur " . $product->name . " :\n\n"
        ];
        $message = ($userName && rand(0, 1) ? "$userName, " : "") . $introMessages[array_rand($introMessages)];
        
        // Prix
        if (preg_match('/\b(prix|coût|tarif)/i', $t)) {
            $message .= "💰 Prix : " . number_format($product->price, 0, ',', ' ') . " FCFA";
            if ($product->old_price && $product->old_price > $product->price) {
                $message .= " (au lieu de " . number_format($product->old_price, 0, ',', ' ') . " FCFA)";
                if ($product->discount_percentage) {
                    $message .= " - " . $product->discount_percentage . "% de réduction";
                }
            }
            $message .= "\n";
        }
        
        // Stock
        if (preg_match('/\b(stock|disponibilité|disponible)/i', $t)) {
            $message .= "📦 Stock : " . ($product->stock > 0 ? $product->stock . " unités disponibles" : "Rupture de stock") . "\n";
        }
        
        // Garantie
        if (preg_match('/\b(garantie)/i', $t) && $product->warranty) {
            $message .= "🛡️ Garantie : " . $product->warranty . "\n";
        }
        
        // Caractéristiques générales
        if (preg_match('/\b(caractéristiques|spécifications|spécificités|détails|infos|informations)/i', $t)) {
            $message .= "📋 Caractéristiques :\n";
            if ($product->brand) $message .= "• Marque : " . $product->brand . "\n";
            if ($product->model) $message .= "• Modèle : " . $product->model . "\n";
            if ($product->description) {
                $desc = strip_tags($product->description);
                $message .= "• Description : " . mb_substr($desc, 0, 200) . (mb_strlen($desc) > 200 ? '...' : '') . "\n";
            }
            if ($product->attributes && is_array($product->attributes)) {
                foreach ($product->attributes as $key => $value) {
                    if (is_string($value) || is_numeric($value)) {
                        $message .= "• " . ucfirst($key) . " : " . $value . "\n";
                    }
                }
            }
        }
        
        // Note et avis
        if (preg_match('/\b(note|avis|commentaires|évaluation)/i', $t)) {
            if ($product->rating > 0) {
                $message .= "⭐ Note : " . number_format($product->rating, 1) . "/5";
                if ($product->reviews_count > 0) {
                    $message .= " (" . $product->reviews_count . " avis)";
                }
                $message .= "\n";
            } else {
                $message .= "⭐ Aucun avis pour le moment\n";
            }
        }

        // Transformer l'image en URL complète
        $productArray = $product->toArray();
        $productArray['image'] = $product->first_image_url ?: asset('images/produit.jpg');
        
        return [
            'message' => $message,
            'items' => [$productArray]
        ];
    }

    /**
     * Répondre aux questions sur les catégories
     */
    private function answerCategoryQuestion(string $text): ?string
    {
        $categories = Category::active()
            ->with(['subcategories' => function($query) {
                $query->where('is_active', true)->orderBy('order')->orderBy('name');
            }])
            ->orderBy('order')
            ->get();
        
        if ($categories->isEmpty()) {
            $userName = session('ai_user_name');
            $emptyMessages = [
                "Aucune catégorie disponible pour le moment.",
                "Désolé, il n'y a pas de catégories disponibles actuellement.",
                "Aucune catégorie n'est disponible pour le moment."
            ];
            return ($userName ? "$userName, " : "") . $emptyMessages[array_rand($emptyMessages)];
        }

        $userName = session('ai_user_name');
        $categoryIntros = [
            "Voici nos catégories de produits :\n\n",
            "Parfait ! Voici toutes nos catégories disponibles :\n\n",
            "Voici la liste de nos catégories :\n\n",
            "Nous avons les catégories suivantes :\n\n"
        ];
        $message = ($userName && rand(0, 1) ? "$userName, " : "") . $categoryIntros[array_rand($categoryIntros)];
        foreach ($categories as $category) {
            $message .= "📁 " . $category->name;
            if ($category->subcategories->isNotEmpty()) {
                $subNames = $category->subcategories->pluck('name')->take(5)->implode(', ');
                $message .= " (" . $subNames;
                if ($category->subcategories->count() > 5) {
                    $message .= " et " . ($category->subcategories->count() - 5) . " autres";
                }
                $message .= ")";
            }
            $message .= "\n";
        }

        return $message;
    }

    /**
     * Répondre aux questions sur les avis
     */
    private function answerReviewQuestion(string $text, array $params): ?string
    {
        $productName = $params['product_name'] ?? null;
        
        if ($productName) {
            // Avis sur un produit spécifique
            $product = Product::active()
                ->where(function($q) use ($productName) {
                    $q->where('name', 'like', '%' . $productName . '%')
                      ->orWhere('brand', 'like', '%' . $productName . '%');
                })
                ->first();

            if (!$product) {
                $userName = session('ai_user_name');
                $notFoundMessages = [
                    "Je n'ai pas trouvé de produit correspondant à \"$productName\".",
                    "Désolé, aucun produit ne correspond à \"$productName\".",
                    "Je ne trouve pas de produit nommé \"$productName\"."
                ];
                return ($userName ? "$userName, " : "") . $notFoundMessages[array_rand($notFoundMessages)];
            }

            $reviews = Review::where('product_id', $product->id)
                ->approved()
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $userName = session('ai_user_name');
            $reviewIntros = [
                "Avis sur " . $product->name . " :\n\n",
                "Voici les avis clients pour " . $product->name . " :\n\n",
                "Les avis sur " . $product->name . " :\n\n"
            ];
            $message = ($userName && rand(0, 1) ? "$userName, " : "") . $reviewIntros[array_rand($reviewIntros)];
            if ($product->rating > 0) {
                $message .= "⭐ Note moyenne : " . number_format($product->rating, 1) . "/5 (" . $product->reviews_count . " avis)\n\n";
            }

            if ($reviews->isEmpty()) {
                $message .= "Aucun avis pour le moment.";
            } else {
                foreach ($reviews as $review) {
                    $stars = str_repeat('⭐', $review->rating) . str_repeat('☆', 5 - $review->rating);
                    $message .= $stars . " " . ($review->title ?: 'Avis') . "\n";
                    if ($review->comment) {
                        $comment = mb_substr($review->comment, 0, 150);
                        $message .= $comment . (mb_strlen($review->comment) > 150 ? '...' : '') . "\n";
                    }
                    $message .= "\n";
                }
            }

            return $message;
        } else {
            // Avis généraux
            return "Pour voir les avis sur un produit, précisez le nom du produit. Exemple : \"Quels sont les avis sur le Samsung Galaxy?\"";
        }
    }

    /**
     * Répondre aux questions sur les promotions
     */
    private function answerPromotionQuestion(string $text): ?array
    {
        // Produits en promotion (avec discount_percentage > 0)
        $promotions = Product::active()
            ->where('discount_percentage', '>', 0)
            ->orderBy('discount_percentage', 'desc')
            ->limit(10)
            ->get(['id','name','slug','image','images','price','old_price','discount_percentage','brand']);

        // Transformer les images en URLs complètes
        $promotions = $promotions->map(function($product) {
            $productArray = $product->toArray();
            $productArray['image'] = $product->first_image_url ?: asset('images/produit.jpg');
            return $productArray;
        });

        if ($promotions->isEmpty()) {
            return [
                'message' => "Il n'y a pas de promotions en cours pour le moment. Revenez bientôt !",
                'items' => []
            ];
        }

        $message = "🔥 Promotions en cours :\n\n";
        foreach ($promotions->take(5) as $product) {
            $productName = is_array($product) ? $product['name'] : $product->name;
            $productPrice = is_array($product) ? $product['price'] : $product->price;
            $productOldPrice = is_array($product) ? ($product['old_price'] ?? null) : $product->old_price;
            $productDiscount = is_array($product) ? ($product['discount_percentage'] ?? null) : $product->discount_percentage;
            
            $message .= "• " . $productName . " : " . number_format($productPrice, 0, ',', ' ') . " FCFA";
            if ($productOldPrice) {
                $message .= " (au lieu de " . number_format($productOldPrice, 0, ',', ' ') . " FCFA)";
            }
            if ($productDiscount) {
                $message .= " - " . $productDiscount . "% de réduction";
            }
            $message .= "\n";
        }

        if ($promotions->count() > 5) {
            $message .= "\n... et " . ($promotions->count() - 5) . " autres promotions !";
        }

        return [
            'message' => $message,
            'items' => $promotions
        ];
    }

    /**
     * Recherche floue améliorée d'un produit
     */
    private function findProductFuzzy(string $searchTerm): ?Product
    {
        if (strlen($searchTerm) < 2) {
            return null;
        }
        
        $searchLower = mb_strtolower($searchTerm);
        $words = explode(' ', $searchLower);
        $words = array_filter($words, fn($w) => strlen($w) >= 2);
        
        // Stratégie 1: Recherche exacte dans le nom
        $product = Product::active()
            ->where('name', 'like', '%' . $searchTerm . '%')
            ->first();
        
        if ($product) return $product;
        
        // Stratégie 2: Recherche dans la marque
        $product = Product::active()
            ->where('brand', 'like', '%' . $searchTerm . '%')
            ->first();
        
        if ($product) return $product;
        
        // Stratégie 3: Recherche par mots individuels (tous les mots doivent être présents)
        if (count($words) > 1) {
            $query = Product::active();
            foreach ($words as $word) {
                $query->where(function($q) use ($word) {
                    $q->where('name', 'like', '%' . $word . '%')
                      ->orWhere('brand', 'like', '%' . $word . '%')
                      ->orWhere('description', 'like', '%' . $word . '%');
                });
            }
            $product = $query->first();
            if ($product) return $product;
        }
        
        // Stratégie 4: Recherche par au moins un mot significatif
        foreach ($words as $word) {
            if (strlen($word) >= 3) {
                $product = Product::active()
                    ->where(function($q) use ($word) {
                        $q->where('name', 'like', '%' . $word . '%')
                          ->orWhere('brand', 'like', '%' . $word . '%')
                          ->orWhere('model', 'like', '%' . $word . '%');
                    })
                    ->orderBy('views_count', 'desc') // Prioriser les produits populaires
                    ->first();
                
                if ($product) return $product;
            }
        }
        
        return null;
    }

    /**
     * Répondre aux questions sur les favoris
     */
    private function answerFavoritesQuestion(string $text, ?int $userId, ?string $sessionId): ?array
    {
        // Récupérer les favoris selon l'utilisateur ou la session
        $favoritesQuery = Favorite::with('product');
        if ($userId) {
            $favoritesQuery->where('user_id', $userId);
        } elseif ($sessionId) {
            $favoritesQuery->where('session_id', $sessionId);
        } else {
            // Pas d'utilisateur ni de session
            $userName = session('ai_user_name');
            return [
                'message' => ($userName ? "$userName, " : "") . "Vous n'avez pas encore de produits en favoris. Ajoutez-en en cliquant sur le cœur sur les produits qui vous intéressent !",
                'items' => []
            ];
        }
        $favorites = $favoritesQuery->orderBy('created_at', 'desc')->get();
        
        if ($favorites->isEmpty()) {
            $userName = session('ai_user_name');
            $emptyMessages = [
                "Vous n'avez pas encore de produits en favoris. Ajoutez-en en cliquant sur le cœur sur les produits qui vous intéressent !",
                "Votre liste de favoris est vide pour le moment. Parcourez nos produits et ajoutez ceux qui vous plaisent !",
                "Aucun produit dans vos favoris. Explorez notre catalogue et ajoutez vos produits préférés !"
            ];
            return [
                'message' => ($userName ? "$userName, " : "") . $emptyMessages[array_rand($emptyMessages)],
                'items' => []
            ];
        }
        
        $products = $favorites->map(function($favorite) {
            return $favorite->product;
        })->filter()->take(10);
        
        if ($products->isEmpty()) {
            return [
                'message' => "Vos favoris contiennent des produits qui ne sont plus disponibles.",
                'items' => []
            ];
        }
        
        // Transformer les images en URLs complètes
        $productsArray = $products->map(function($product) {
            if (!$product) {
                return null;
            }
            try {
                $productArray = $product->toArray();
                // Utiliser l'accessor first_image_url si disponible
                $productArray['image'] = $product->first_image_url ?? asset('images/produit.jpg');
                return $productArray;
            } catch (\Exception $e) {
                return null;
            }
        })->filter();
        
        $userName = session('ai_user_name');
        $favoriteIntros = [
            "Voici vos produits favoris :\n\n",
            "Parfait ! Voici vos favoris :\n\n",
            "Voici la liste de vos produits favoris :\n\n",
            "J'ai trouvé " . $products->count() . " produit(s) dans vos favoris :\n\n"
        ];
        $message = ($userName && rand(0, 1) ? "$userName, " : "") . $favoriteIntros[array_rand($favoriteIntros)];
        
        return [
            'message' => $message,
            'items' => $productsArray
        ];
    }
    
    /**
     * API endpoint pour obtenir des suggestions basées sur l'historique de vues
     * Peut être appelé depuis n'importe quelle page (hors chat box)
     */
    public function getSuggestions(Request $request)
    {
        try {
            if (!config('kazar_ai.enabled')) {
                return response()->json(['success' => false, 'message' => 'KAZAR I.A désactivée'], 403);
            }
            
            // Récupérer l'utilisateur connecté ou la session
            $user = Auth::user();
            $userId = $user ? $user->id : null;
            $sessionId = $request->session()->getId();
            
            // Récupérer les produits basés sur l'historique de vues
            $products = $this->getProductsFromRecentViews($userId, $sessionId);
            
            if ($products->isEmpty()) {
                // Si aucun historique, proposer des produits populaires
                $products = Product::active()
                    ->inStock()
                    ->orderBy('views_count', 'desc')
                    ->orderBy('discount_percentage', 'desc')
                    ->limit(10)
                    ->get(['id','name','slug','image','images','price','old_price','discount_percentage','brand']);
            }
            
            // Transformer les images en URLs complètes
            $productsArray = $products->map(function($product) {
                $productArray = $product->toArray();
                $productArray['image'] = $product->first_image_url ?? asset('images/produit.jpg');
                $productArray['url'] = route('product-page', $product->slug);
                return $productArray;
            });
            
            return response()->json([
                'success' => true,
                'products' => $productsArray,
                'title' => 'Produits que vous avez récemment consultés',
                'count' => $productsArray->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtenir des produits basés sur l'historique de vues récentes
     */
    private function getProductsFromRecentViews(?int $userId, ?string $sessionId): \Illuminate\Database\Eloquent\Collection
    {
        // Récupérer les IDs des produits récemment consultés
        $recentViews = ProductView::where(function($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->recent(1440) // 24 heures
            ->orderBy('created_at', 'desc')
            ->distinct('product_id')
            ->pluck('product_id')
            ->take(10);
        
        if ($recentViews->isEmpty()) {
            return Product::whereRaw('1 = 0')->get(); // Retourner une collection vide de type Eloquent
        }
        
        // Récupérer les produits actifs correspondants
        $products = Product::active()
            ->whereIn('id', $recentViews)
            ->orderByRaw('FIELD(id, ' . $recentViews->implode(',') . ')')
            ->limit(10)
            ->get(['id','name','slug','image','images','price','old_price','discount_percentage','brand']);
        
        return $products;
    }
    
    /**
     * Système de fallback intelligent - Essayer plusieurs interprétations
     */
    private function tryFallbackInterpretations(string $text, string $normalizedText): ?array
    {
        // Si aucune intention n'a été détectée, essayer des interprétations alternatives
        
        // 1. Peut-être que c'est une recherche de produit mal formulée
        $productKeywords = $this->extractProductKeywords($normalizedText);
        if (!empty($productKeywords)) {
            $products = Product::active()
                ->where(function($q) use ($productKeywords) {
                    foreach ($productKeywords as $kw) {
                        $q->orWhere('name', 'like', '%' . $kw . '%')
                          ->orWhere('description', 'like', '%' . $kw . '%')
                          ->orWhere('tags', 'like', '%' . $kw . '%');
                    }
                })
                ->limit(5)
                ->get(['id','name','slug','image','images','price','old_price','discount_percentage','brand']);
            
            // Transformer les images en URLs complètes
            $products = $products->map(function($product) {
                $productArray = $product->toArray();
                $productArray['image'] = $product->first_image_url ?: asset('images/produit.jpg');
                return $productArray;
            });
            
            if ($products->isNotEmpty()) {
                return [
                    'success' => true,
                    'message' => "Voici des produits qui pourraient vous intéresser :",
                    'items' => $products,
                    'intent' => 'search',
                    'intent_params' => [],
                    'understood' => []
                ];
            }
        }
        
        // 2. Peut-être une question FAQ mal formulée
        $faqMatch = FAQ::findMatching($normalizedText);
        if ($faqMatch) {
            return [
                'success' => true,
                'message' => $faqMatch->answer,
                'items' => [],
                'intent' => 'faq',
                'intent_params' => ['faq_id' => $faqMatch->id],
                'understood' => []
            ];
        }
        
        // 3. Réponse générique mais utile
        return [
            'success' => true,
            'message' => "Je n'ai pas bien compris votre question. Pouvez-vous reformuler ?\n\nJe peux vous aider avec :\n• Recherche de produits\n• Informations sur les produits\n• Catégories disponibles\n• Promotions en cours\n• Avis clients\n• Questions sur la livraison, paiement, etc.",
            'items' => [],
            'intent' => 'fallback',
            'intent_params' => [],
            'understood' => []
        ];
    }
}


