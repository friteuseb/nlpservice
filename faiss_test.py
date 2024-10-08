import numpy as np
import faiss

# Générer quelques embeddings aléatoires (par exemple, 10 vecteurs avec 128 dimensions)
dimension = 128
num_vectors = 10
embeddings = np.random.random((num_vectors, dimension)).astype('float32')

# Créer l'index FAISS (Index Flat L2 pour commencer)
index = faiss.IndexFlatL2(dimension)

# Ajouter les embeddings à l'index
index.add(embeddings)

# Simuler une requête de similarité avec un autre vecteur
query_vector = np.random.random((1, dimension)).astype('float32')

# Rechercher les 5 vecteurs les plus proches
distances, indices = index.search(query_vector, 5)

print("Indices des vecteurs les plus proches :", indices)
print("Distances des vecteurs les plus proches :", distances)
