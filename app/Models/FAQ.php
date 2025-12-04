<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    use HasFactory;

    protected $table = 'faqs';

    protected $fillable = [
        'question',
        'answer',
        'keywords',
        'category',
        'order',
        'is_active',
        'views_count'
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'views_count' => 'integer',
    ];

    /**
     * Scope pour les FAQs actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour une catégorie spécifique
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Trouver une FAQ correspondant à un texte
     */
    public static function findMatching($text)
    {
        $textLower = mb_strtolower(trim($text));
        if (empty($textLower)) {
            return null;
        }
        
        // Recherche exacte dans la question (priorité à la question, pas à la réponse)
        $exactMatch = self::active()
            ->whereRaw('LOWER(question) LIKE ?', ['%' . $textLower . '%'])
            ->orderBy('views_count', 'desc')
            ->first();
        
        if ($exactMatch) {
            $exactMatch->increment('views_count');
            return $exactMatch;
        }
        
        // Si pas trouvé dans la question, chercher dans la réponse (mais avec moins de priorité)
        $answerMatch = self::active()
            ->whereRaw('LOWER(answer) LIKE ?', ['%' . $textLower . '%'])
            ->whereRaw('LOWER(question) NOT LIKE ?', ['%' . $textLower . '%']) // Exclure si déjà dans la question
            ->orderBy('views_count', 'desc')
            ->first();
        
        // Ne retourner la réponse que si c'est un mot-clé très spécifique
        if ($answerMatch && strlen($textLower) >= 6) {
            $answerMatch->increment('views_count');
            return $answerMatch;
        }

        // Recherche par mots-clés
        $keywords = self::tokenize($textLower);
        if (empty($keywords)) {
            return null;
        }
        
        $bestMatch = null;
        $bestScore = 0;

        foreach (self::active()->get() as $faq) {
            $score = 0;
            $faqKeywords = $faq->keywords ?? [];
            $faqKeywordsLower = array_map('mb_strtolower', $faqKeywords);
            
            // Si le texte est un seul mot et qu'il correspond exactement à un mot-clé, score très élevé
            if (count($keywords) === 1) {
                $singleKeyword = $keywords[0];
                $questionLower = mb_strtolower($faq->question);
                
                // Priorité absolue : correspondance exacte dans les mots-clés ET dans la question
                if (in_array($singleKeyword, $faqKeywordsLower, true) && str_contains($questionLower, $singleKeyword)) {
                    $score += 15; // Score très élevé pour correspondance parfaite
                } elseif (in_array($singleKeyword, $faqKeywordsLower, true)) {
                    $score += 10; // Score élevé pour correspondance dans les mots-clés
                } elseif (strlen($singleKeyword) >= 4 && str_contains($questionLower, $singleKeyword)) {
                    $score += 8; // Score moyen si dans la question mais pas dans les mots-clés
                }
            }
            
            // Compter les correspondances de mots-clés
            foreach ($keywords as $keyword) {
                if (strlen($keyword) < 3) continue;
                
                // Correspondance exacte dans les mots-clés
                if (in_array($keyword, $faqKeywordsLower, true)) {
                    $score += 3;
                }
                
                // Correspondance partielle dans les mots-clés
                foreach ($faqKeywordsLower as $faqKw) {
                    if (str_contains($faqKw, $keyword) || str_contains($keyword, $faqKw)) {
                        $score += 2;
                        break;
                    }
                }
            }
            
            // Vérifier aussi si des mots-clés sont dans la question
            $questionLower = mb_strtolower($faq->question);
            foreach ($keywords as $keyword) {
                if (strlen($keyword) >= 3 && str_contains($questionLower, $keyword)) {
                    $score += 1;
                }
            }
            
            // Bonus si plusieurs mots correspondent
            $matchedCount = 0;
            foreach ($keywords as $keyword) {
                if (strlen($keyword) >= 3) {
                    foreach ($faqKeywordsLower as $faqKw) {
                        if (str_contains($faqKw, $keyword) || str_contains($keyword, $faqKw)) {
                            $matchedCount++;
                            break;
                        }
                    }
                }
            }
            if ($matchedCount >= 2) {
                $score += 2; // Bonus pour plusieurs correspondances
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $faq;
            }
        }

        // Seuil ajusté : pour un seul mot, besoin d'un score élevé (>= 8), sinon >= 2
        $threshold = (count($keywords) === 1) ? 8 : 2;
        if ($bestMatch && $bestScore >= $threshold) {
            $bestMatch->increment('views_count');
            return $bestMatch;
        }

        return null;
    }

    /**
     * Tokeniser un texte (extraire les mots significatifs)
     */
    private static function tokenize($text)
    {
        // Stopwords réduits - garder les mots de question importants
        $stopwords = [
            'le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'a', 'à', 'au', 'aux',
            'pour', 'avec', 'sans', 'sur', 'en', 'par', 'mon', 'ma', 'mes', 'ton', 'ta', 'tes',
            'son', 'sa', 'ses', 'nos', 'vos', 'leurs', 'je', 'tu', 'il', 'elle', 'on', 'nous',
            'vous', 'ils', 'elles', 'ce', 'cet', 'cette', 'ces', 'plus', 'moins', 'très', 'tres',
            'bon', 'bien', 'meilleur', 'nouveau', 'neuf', 'neuve', 'neufs', 'neuves', 'chez',
            'vers', 'dans', 'entre', 'the', 'and', 'est', 'sont', 'être', 'avoir', 'faire'
            // Note: on garde 'comment', 'quels', 'quelles', 'quel', 'quelle', 'combien' car ils sont importants pour les questions
        ];
        
        $words = preg_split('/[^a-zà-ÿ0-9]+/u', $text);
        $tokens = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            // Réduire la longueur minimale à 2 caractères pour capturer plus de mots
            if (strlen($word) >= 2 && !in_array($word, $stopwords)) {
                $tokens[] = $word;
            }
        }
        
        return array_unique($tokens);
    }
}
