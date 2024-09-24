# Méthodes de calcul de similarité textuelle

Ce document explique les différentes méthodes de calcul de similarité textuelle disponibles dans notre API, avec des exemples de requêtes pour chaque méthode.

## 1. Similarité cosinus (méthode par défaut)

La similarité cosinus mesure l'angle entre deux vecteurs de mots dans un espace multidimensionnel.

**Fonctionnement :**
1. Les textes sont convertis en vecteurs TF-IDF.
2. Le cosinus de l'angle entre ces vecteurs est calculé.
3. Plus l'angle est petit, plus les textes sont similaires.

**Exemple de requête :**
```json
{
  "text1": "SGUgbGlrZXMgdG8gcGxheSBmb290YmFsbC4=",
  "text2": "U2hlIGVuam95cyBwbGF5aW5nIHNvY2Nlci4=",
  "method": "cosine"
}
```

## 2. Similarité euclidienne

La distance euclidienne mesure la distance "en ligne droite" entre deux points dans un espace multidimensionnel.

**Fonctionnement :**
1. Les textes sont convertis en vecteurs TF-IDF.
2. La distance euclidienne entre ces vecteurs est calculée.
3. Cette distance est convertie en similarité : 1 / (1 + distance).

**Exemple de requête :**
```json
{
  "text1": "TGEgbHVuZSBicmlsbGUgZGFucyBsZSBjaWVsIG5vY3R1cm5lLg==",
  "text2": "TGVzIMOpdG9pbGVzIHNjaW50aWxsZW50IGRhbnMgbGEgbnVpdC4=",
  "method": "euclidean"
}
```

## 3. Similarité de Manhattan

La distance de Manhattan mesure la distance entre deux points en suivant un chemin "en grille".

**Fonctionnement :**
1. Les textes sont convertis en vecteurs TF-IDF.
2. La distance de Manhattan entre ces vecteurs est calculée.
3. Cette distance est convertie en similarité : 1 / (1 + distance).

**Exemple de requête :**
```json
{
  "text1": "TGVzIG9pc2VhdXggY2hhbnRlbnQgYXUgcHJpbnRlbXBzLg==",
  "text2": "TGVzIGZsZXVycyBzJ8OpcGFub3Vpc3NlbnQgYXUgcHJpbnRlbXBzLg==",
  "method": "manhattan"
}
```

## 4. Similarité de Jaccard

L'indice de Jaccard mesure la similarité entre deux ensembles de mots.

**Fonctionnement :**
1. Les textes sont divisés en ensembles de mots uniques.
2. La similarité est calculée comme : |intersection| / |union|.

**Exemple de requête :**
```json
{
  "text1": "TGUgY2hhdCBkb3J0IHN1ciBsZSB0YXBpcy4=",
  "text2": "TGUgY2hpZW4gam91ZSBkYW5zIGxlIGphcmRpbi4=",
  "method": "jaccard"
}
```

## 5. Similarité BLEU

BLEU (Bilingual Evaluation Understudy) est une métrique initialement conçue pour évaluer la qualité des traductions automatiques.

**Fonctionnement :**
1. Les textes sont tokenisés en mots.
2. BLEU compare les n-grammes communs entre les deux textes.
3. Un score est calculé basé sur la précision des correspondances.

**Exemple de requête :**
```json
{
  "text1": "Qm9uam91ciwgY29tbWVudCBhbGxlei12b3VzID8=",
  "text2": "U2FsdXQsIGNvbW1lbnQgdmEtdHUgP8Ow",
  "method": "bleu"
}
```

## Comparaison visuelle des méthodes

Pour mieux comprendre les différences entre ces méthodes, voici un schéma illustratif :

<antArtifact identifier="similarity-methods-diagram" type="image/svg+xml" title="Diagramme des méthodes de similarité">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500">
  <rect width="800" height="500" fill="#f0f0f0"/>
  
  <!-- Cosine Similarity -->
  <g transform="translate(100,100)">
    <text x="0" y="-10" font-size="16" font-weight="bold">Cosine Similarity</text>
    <line x1="0" y1="0" x2="100" y2="0" stroke="black" stroke-width="2"/>
    <line x1="0" y1="0" x2="70" y2="70" stroke="black" stroke-width="2"/>
    <path d="M 0 0 Q 50 0 35 35" fill="none" stroke="red" stroke-width="2"/>
    <text x="40" y="25" fill="red">θ</text>
  </g>
  
  <!-- Euclidean Distance -->
  <g transform="translate(350,100)">
    <text x="0" y="-10" font-size="16" font-weight="bold">Euclidean Distance</text>
    <circle cx="0" cy="0" r="3" fill="blue"/>
    <circle cx="100" cy="70" r="3" fill="blue"/>
    <line x1="0" y1="0" x2="100" y2="70" stroke="red" stroke-width="2" stroke-dasharray="5,5"/>
  </g>
  
  <!-- Manhattan Distance -->
  <g transform="translate(600,100)">
    <text x="0" y="-10" font-size="16" font-weight="bold">Manhattan Distance</text>
    <circle cx="0" cy="0" r="3" fill="blue"/>
    <circle cx="100" cy="70" r="3" fill="blue"/>
    <path d="M 0 0 L 100 0 L 100 70" fill="none" stroke="red" stroke-width="2"/>
  </g>
  
  <!-- Jaccard Similarity -->
  <g transform="translate(100,300)">
    <text x="0" y="-10" font-size="16" font-weight="bold">Jaccard Similarity</text>
    <circle cx="50" cy="50" r="50" fill="none" stroke="blue" stroke-width="2"/>
    <circle cx="90" cy="50" r="50" fill="none" stroke="red" stroke-width="2"/>
    <path d="M 70 50 A 20 20 0 0 0 70 50" fill="#purple" fill-opacity="0.3"/>
  </g>
  
  <!-- BLEU Score -->
  <g transform="translate(350,300)">
    <text x="0" y="-10" font-size="16" font-weight="bold">BLEU Score</text>
    <rect x="0" y="0" width="200" height="30" fill="lightblue" stroke="blue"/>
    <rect x="0" y="40" width="200" height="30" fill="lightgreen" stroke="green"/>
    <rect x="20" y="80" width="160" height="30" fill="purple" fill-opacity="0.3" stroke="purple"/>
    <text x="100" y="20" text-anchor="middle">Reference Text</text>
    <text x="100" y="60" text-anchor="middle">Candidate Text</text>
    <text x="100" y="100" text-anchor="middle">Matching N-grams</text>
  </g>
</svg>


# Exemples de requêtes curl pour le calcul de similarité

Ce document fournit des exemples de requêtes curl pour chaque méthode de calcul de similarité disponible dans notre API. Ces exemples sont prêts à être copiés et collés dans votre terminal.

## Endpoint de l'API

L'endpoint correct pour toutes les requêtes est :

```
https://nlpservice.semantic-suggestion.com/api/analyze
```

## 1. Similarité cosinus (méthode par défaut)

```bash
curl -X POST https://nlpservice.semantic-suggestion.com/api/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "text1": "SGUgbGlrZXMgdG8gcGxheSBmb290YmFsbC4=",
    "text2": "U2hlIGVuam95cyBwbGF5aW5nIHNvY2Nlci4=",
    "method": "cosine"
  }'
```

## 2. Similarité euclidienne

```bash
curl -X POST https://nlpservice.semantic-suggestion.com/api/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "text1": "TGEgbHVuZSBicmlsbGUgZGFucyBsZSBjaWVsIG5vY3R1cm5lLg==",
    "text2": "TGVzIMOpdG9pbGVzIHNjaW50aWxsZW50IGRhbnMgbGEgbnVpdC4=",
    "method": "euclidean"
  }'
```

## 3. Similarité de Manhattan

```bash
curl -X POST https://nlpservice.semantic-suggestion.com/api/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "text1": "TGVzIG9pc2VhdXggY2hhbnRlbnQgYXUgcHJpbnRlbXBzLg==",
    "text2": "TGVzIGZsZXVycyBzJ8OpcGFub3Vpc3NlbnQgYXUgcHJpbnRlbXBzLg==",
    "method": "manhattan"
  }'
```

## 4. Similarité de Jaccard

```bash
curl -X POST https://nlpservice.semantic-suggestion.com/api/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "text1": "TGUgY2hhdCBkb3J0IHN1ciBsZSB0YXBpcy4=",
    "text2": "TGUgY2hpZW4gam91ZSBkYW5zIGxlIGphcmRpbi4=",
    "method": "jaccard"
  }'
```

## 5. Similarité BLEU

```bash
curl -X POST https://nlpservice.semantic-suggestion.com/api/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "text1": "Qm9uam91ciwgY29tbWVudCBhbGxlei12b3VzID8=",
    "text2": "U2FsdXQsIGNvbW1lbnQgdmEtdHUgPw==",
    "method": "bleu"
  }'
```

Note : Les exemples de textes encodés en Base64 et les instructions pour l'encodage/décodage restent les mêmes que dans la version précédente.