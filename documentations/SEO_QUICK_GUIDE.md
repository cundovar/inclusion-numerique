# 🎯 Guide rapide SEO

## ✅ Statut: Opérationnel

Toutes les améliorations SEO sont actives et fonctionnelles.

---

## 🔗 Liens utiles

### Sitemap XML
```
http://localhost:8080/sitemap.xml
```
**182+ pages indexées** automatiquement

### Documentation complète
Consultez `SEO_IMPROVEMENTS.md` pour tous les détails

---

## 📝 Actions rapides

### Vérifier le SEO d'une page
```bash
# Exemple: tester la page "symfony-basics"
curl -s http://localhost:8080/spa/pages/symfony-basics | grep -E "<title>|<meta name=\"description\""
```

### Vider le cache (après modification)
```bash
docker compose exec php php bin/console cache:clear
```

### Voir toutes les pages dans le sitemap
```bash
curl -s http://localhost:8080/sitemap.xml | grep "<loc>" | wc -l
```

---

## 🆕 Créer du SEO personnalisé

### Via EasyAdmin (Recommandé)
1. Accédez à `http://localhost:8080/admin`
2. Allez dans **Seo** > **Create Seo**
3. Remplissez:
   - **Title**: "Cours Symfony - Guide Complet 2025"
   - **Meta Description**: "Apprenez Symfony avec notre cours complet..."
   - **OG Title**: Pour Facebook/Twitter
   - **OG Description**: Pour les réseaux sociaux
4. Associez le SEO à une **Page** existante

### Par programmation
Le SEO est généré automatiquement si vous ne créez pas d'entrée personnalisée.

---

## 🧪 Tests SEO

### Test 1: Sitemap XML
```bash
curl http://localhost:8080/sitemap.xml
```
✅ Doit retourner un XML valide avec vos pages

### Test 2: Meta tags d'une page
```bash
curl -s http://localhost:8080/spa/pages/intro-symfony | grep -o '<meta[^>]*>'
```
✅ Doit afficher les balises meta

### Test 3: Données structurées
```bash
curl -s http://localhost:8080/spa/pages/intro-symfony | grep -A 15 'application/ld+json'
```
✅ Doit afficher le JSON-LD

---

## 🌐 Mise en production

### Avant de déployer

1. **Modifier l'URL de base** dans `config/services.yaml`:
```yaml
parameters:
    app.url: 'https://votre-domaine-reel.com'
```

2. **Vider le cache**:
```bash
docker compose exec php php bin/console cache:clear --env=prod
```

3. **Soumettre à Google**:
   - Allez sur https://search.google.com/search-console
   - Ajoutez votre propriété
   - Soumettez le sitemap: `https://votre-domaine.com/sitemap.xml`

---

## 📊 Ce qui a été ajouté

| Fonctionnalité | Statut | Fichier |
|----------------|--------|---------|
| Relation Page ↔ SEO | ✅ | `src/Entity/Page.php` |
| Support slugs | ✅ | `src/Service/SeoService.php` |
| JSON-LD | ✅ | `templates/base2.html.twig` |
| Sitemap XML | ✅ | `src/Controller/SitemapController.php` |
| Composable Vue | ✅ | `front/src/composables/useSeo.js` |
| Migration BDD | ✅ | Schéma mis à jour |

---

## ❓ FAQ

**Q: Le sitemap ne fonctionne pas?**
A: Videz le cache avec `docker compose exec php php bin/console cache:clear`

**Q: Comment ajouter une image Open Graph?**
A: Via EasyAdmin, éditez votre Seo et remplissez le champ `ogImage` avec l'URL complète de l'image (1200x630px recommandé)

**Q: Les données structurées n'apparaissent pas?**
A: Vérifiez que le champ `structuredData` n'est pas vide dans votre entité Seo. S'il est vide, il sera généré automatiquement.

**Q: Combien de temps avant que Google indexe?**
A: Entre 1 jour et 2 semaines après soumission du sitemap dans Search Console.

---

**Mise à jour:** 28 novembre 2025
**Statut:** ✅ Tout fonctionne
**Support:** Consultez `SEO_IMPROVEMENTS.md` pour plus de détails
