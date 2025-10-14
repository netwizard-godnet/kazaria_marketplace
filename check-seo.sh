#!/bin/bash

# Script de vérification SEO KAZARIA

echo "🔍 Vérification SEO KAZARIA"
echo "=========================="
echo ""

# Détecter l'URL du site
if [ -z "$1" ]; then
    SITE_URL="https://kazaria-ci.com"
else
    SITE_URL="$1"
fi

echo "📊 Site testé : $SITE_URL"
echo ""

# Test 1 : Robots.txt
echo "1️⃣  Test robots.txt"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/robots.txt")
if [ "$HTTP_CODE" = "200" ]; then
    echo "   ✅ robots.txt accessible (HTTP $HTTP_CODE)"
    echo "   📄 Contenu :"
    curl -s "$SITE_URL/robots.txt" | head -10
else
    echo "   ❌ robots.txt non accessible (HTTP $HTTP_CODE)"
fi
echo ""

# Test 2 : Sitemap
echo "2️⃣  Test sitemap.xml"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/sitemap.xml")
if [ "$HTTP_CODE" = "200" ]; then
    echo "   ✅ sitemap.xml accessible (HTTP $HTTP_CODE)"
    URL_COUNT=$(curl -s "$SITE_URL/sitemap.xml" | grep -c "<url>")
    echo "   📊 Nombre d'URLs dans le sitemap : $URL_COUNT"
else
    echo "   ❌ sitemap.xml non accessible (HTTP $HTTP_CODE)"
fi
echo ""

# Test 3 : Balises META
echo "3️⃣  Test balises META"
TITLE=$(curl -s "$SITE_URL" | grep -o "<title>.*</title>" | head -1)
if [ -n "$TITLE" ]; then
    echo "   ✅ Title trouvé : $TITLE"
else
    echo "   ❌ Aucun title trouvé"
fi

DESC=$(curl -s "$SITE_URL" | grep -o '<meta name="description"[^>]*>' | head -1)
if [ -n "$DESC" ]; then
    echo "   ✅ Meta description trouvée"
else
    echo "   ❌ Aucune meta description"
fi

KEYWORDS=$(curl -s "$SITE_URL" | grep -o '<meta name="keywords"[^>]*>' | head -1)
if [ -n "$KEYWORDS" ]; then
    echo "   ✅ Meta keywords trouvés"
else
    echo "   ⚠️  Aucun meta keywords (optionnel)"
fi
echo ""

# Test 4 : Open Graph
echo "4️⃣  Test Open Graph (réseaux sociaux)"
OG_COUNT=$(curl -s "$SITE_URL" | grep -c 'property="og:')
if [ "$OG_COUNT" -gt 0 ]; then
    echo "   ✅ $OG_COUNT balises Open Graph trouvées"
else
    echo "   ❌ Aucune balise Open Graph"
fi
echo ""

# Test 5 : Structured Data (JSON-LD)
echo "5️⃣  Test Structured Data (JSON-LD)"
JSON_LD=$(curl -s "$SITE_URL" | grep -c 'application/ld+json')
if [ "$JSON_LD" -gt 0 ]; then
    echo "   ✅ Structured Data présente"
else
    echo "   ⚠️  Aucune structured data (recommandé)"
fi
echo ""

# Test 6 : HTTPS
echo "6️⃣  Test HTTPS"
if [[ $SITE_URL == https://* ]]; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL")
    if [ "$HTTP_CODE" = "200" ]; then
        echo "   ✅ HTTPS actif et fonctionnel"
    else
        echo "   ⚠️  HTTPS configuré mais erreur HTTP $HTTP_CODE"
    fi
else
    echo "   ❌ Site en HTTP (HTTPS recommandé pour le SEO)"
fi
echo ""

# Test 7 : Temps de réponse
echo "7️⃣  Test vitesse"
RESPONSE_TIME=$(curl -o /dev/null -s -w '%{time_total}\n' "$SITE_URL")
echo "   ⏱️  Temps de réponse : ${RESPONSE_TIME}s"
if (( $(echo "$RESPONSE_TIME < 2.0" | bc -l) )); then
    echo "   ✅ Temps de réponse bon (< 2s)"
elif (( $(echo "$RESPONSE_TIME < 4.0" | bc -l) )); then
    echo "   ⚠️  Temps de réponse moyen (2-4s)"
else
    echo "   ❌ Temps de réponse lent (> 4s)"
fi
echo ""

# Test 8 : Mobile-friendly
echo "8️⃣  Test Mobile-friendly"
VIEWPORT=$(curl -s "$SITE_URL" | grep -c '<meta name="viewport"')
if [ "$VIEWPORT" -gt 0 ]; then
    echo "   ✅ Meta viewport présent (mobile-friendly)"
else
    echo "   ❌ Meta viewport manquant (important pour mobile)"
fi
echo ""

# Test 9 : Favicon
echo "9️⃣  Test Favicon"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/favicon.ico")
if [ "$HTTP_CODE" = "200" ]; then
    echo "   ✅ Favicon présent"
else
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/favicon.png")
    if [ "$HTTP_CODE" = "200" ]; then
        echo "   ✅ Favicon PNG présent"
    else
        echo "   ⚠️  Aucun favicon trouvé"
    fi
fi
echo ""

# Résumé
echo "=========================="
echo "📋 RÉSUMÉ"
echo "=========================="
echo ""
echo "✅ Actions recommandées :"
echo "   1. Soumettre le sitemap à Google Search Console"
echo "   2. Soumettre le sitemap à Bing Webmaster"
echo "   3. Créer Google My Business"
echo "   4. Optimiser la vitesse si > 2s"
echo "   5. Créer du contenu régulièrement"
echo ""
echo "📊 Résultats détaillés dans le rapport ci-dessus"
echo ""
echo "📖 Consultez GUIDE_REFERENCEMENT_SEO.md pour plus d'infos"
echo ""
