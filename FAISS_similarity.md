        +---------------------+            +---------------------+
        |                     |            |                     |
        |      TYPO3          | <---API--->|      Python API     |
        |                     |            |                     |
        +---------------------+            +---------------------+
                    |                                      |
                    | Envoie des lots de textes            | Génère les embeddings 
                    | multilingues (par ex. 100 textes)    | à partir des textes reçus
                    v                                      v
        +---------------------------------------------------------------+
        |                    Python Service (Text Processing)           |
        |                                                               |
        | - Reçoit les textes et les prétraite (tokenisation, etc.)     |
        | - Génère les embeddings pour chaque texte via spaCy/sentence  | 
        |   transformers                                                |
        | - Envoie les embeddings à FAISS pour indexation et recherche  |
        +---------------------------------------------------------------+
                    |                                     |
                    | Transfert les embeddings à FAISS    | Demande à FAISS les textes similaires
                    v                                     v
        +-----------------------------+      +-----------------------------+
        |                             |      |                             |
        |         FAISS Index         |<---->|   FAISS Similarity Search   |
        | (Stocke les embeddings)     |      | (Recherche les textes       |
        |                             |      |  similaires dans l'index)   |
        +-----------------------------+      +-----------------------------+
                    ^                                     |
                    | Renvoie les indices des textes      | Retourne les résultats des textes
                    | les plus similaires                 | similaires à Python
                    |                                     v
        +--------------------------------------------------------------+
        |                    Python Service (Similarity Results)       |
        |                                                              |
        | - Reçoit les résultats de FAISS (indices des textes)         |
        | - Utilise les indices pour récupérer les titres/descriptions |
        |   correspondants aux articles similaires                     |
        | - Renvoie les articles similaires à TYPO3                    |
        +--------------------------------------------------------------+
                    ^
                    | Renvoie les articles similaires
                    v
        +-------------------------+
        |                         |
        |      TYPO3              |
        |  (Suggestion d'articles)|
        |  Affiche les résultats  |
        +-------------------------+
