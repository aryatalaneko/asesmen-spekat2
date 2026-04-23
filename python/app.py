"""
Flask Microservice: K-Means Clustering + NLP Essay Scoring
SMP Katolik St. Johanis Laikit - Assessment System
Jalankan: python app.py
Port: 5000
"""

from flask import Flask, request, jsonify
import numpy as np
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler
import re
import math
from collections import Counter

app = Flask(__name__)


# ============================================================
# ENDPOINT 1: K-MEANS CLUSTERING
# POST /cluster
# Input: { "students": [{"user_id": 1, "final_score": 85, "pg_score": 60, "essay_score": 25}, ...] }
# Output: { "results": [{"user_id": 1, "final_score": 85, "cluster_number": 0, "cluster_label": "aman"}, ...] }
# ============================================================
@app.route('/cluster', methods=['POST'])
def cluster():
    try:
        data = request.get_json()
        students = data.get('students', [])

        if len(students) < 3:
            return jsonify({'error': 'Minimal 3 siswa diperlukan untuk K-Means'}), 400

        # Buat feature matrix [final_score, pg_score, essay_score]
        features = np.array([
            [s.get('final_score', 0), s.get('pg_score', 0), s.get('essay_score', 0)]
            for s in students
        ])

        # Normalisasi fitur
        scaler = StandardScaler()
        features_scaled = scaler.fit_transform(features)

        # K-Means dengan k=3
        k = min(3, len(students))
        kmeans = KMeans(n_clusters=k, random_state=42, n_init=10)
        kmeans.fit(features_scaled)

        labels = kmeans.labels_

        # Tentukan label cluster berdasarkan rata-rata nilai
        # (cluster dengan nilai tertinggi = Aman, terendah = Risiko Tinggi)
        cluster_means = {}
        for cluster_idx in range(k):
            mask = labels == cluster_idx
            cluster_means[cluster_idx] = float(np.mean(features[mask, 0]))  # rata-rata final_score

        # Urutkan: nilai tertinggi=aman, menengah=bimbingan, terendah=risiko_tinggi
        sorted_clusters = sorted(cluster_means.items(), key=lambda x: x[1], reverse=True)
        label_map = {}
        cluster_label_names = ['aman', 'bimbingan', 'risiko_tinggi']
        for i, (cluster_idx, _) in enumerate(sorted_clusters):
            label_map[cluster_idx] = cluster_label_names[i] if i < 3 else 'bimbingan'

        results = []
        for i, student in enumerate(students):
            cluster_num = int(labels[i])
            results.append({
                'user_id':        student['user_id'],
                'final_score':    student.get('final_score', 0),
                'cluster_number': cluster_num,
                'cluster_label':  label_map.get(cluster_num, 'bimbingan'),
            })

        return jsonify({
            'results':      results,
            'n_clusters':   k,
            'cluster_means': {label_map[idx]: round(mean, 2) for idx, mean in sorted_clusters}
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500


# ============================================================
# ENDPOINT 2: NLP ESSAY SCORING (TF-IDF Cosine Similarity)
# POST /score-essay
# Input: { "jawaban_siswa": "...", "kunci_jawaban": "..." }
# Output: { "similarity_score": 0.75, "method": "tfidf_cosine" }
# ============================================================
def tokenize(text):
    """Simple tokenizer: lowercase, remove non-alphabet, split"""
    text = text.lower()
    text = re.sub(r'[^a-zA-Z0-9\s]', ' ', text)
    tokens = text.split()

    # Simple stopword removal (Bahasa Indonesia)
    stopwords_id = {
        'yang', 'dan', 'adalah', 'ini', 'itu', 'di', 'ke', 'dari', 'dengan',
        'untuk', 'pada', 'dalam', 'akan', 'dapat', 'tidak', 'ada', 'juga',
        'oleh', 'atau', 'atas', 'sebagai', 'karena', 'sehingga', 'sebuah',
        'bisa', 'sudah', 'lebih', 'setiap', 'satu', 'dua', 'jika', 'maka',
        'menjadi', 'memiliki', 'antara', 'bagi', 'pun', 'pula', 'lain',
    }
    return [t for t in tokens if t not in stopwords_id and len(t) > 1]


def compute_tfidf(corpus):
    """Compute TF-IDF from a list of documents"""
    n = len(corpus)
    tokenized = [tokenize(doc) for doc in corpus]

    # IDF
    df = Counter()
    for tokens in tokenized:
        for t in set(tokens):
            df[t] += 1

    idf = {t: math.log(n / (count + 1)) + 1 for t, count in df.items()}

    # TF-IDF vectors
    vectors = []
    for tokens in tokenized:
        tf = Counter(tokens)
        total = len(tokens) if tokens else 1
        vector = {t: (count / total) * idf.get(t, 1) for t, count in tf.items()}
        vectors.append(vector)

    return vectors


def cosine_similarity(v1, v2):
    """Cosine similarity between two dict-vectors"""
    keys = set(v1.keys()) | set(v2.keys())
    dot_product = sum(v1.get(k, 0) * v2.get(k, 0) for k in keys)
    mag1 = math.sqrt(sum(x ** 2 for x in v1.values()))
    mag2 = math.sqrt(sum(x ** 2 for x in v2.values()))
    if mag1 == 0 or mag2 == 0:
        return 0.0
    return dot_product / (mag1 * mag2)


@app.route('/score-essay', methods=['POST'])
def score_essay():
    try:
        data = request.get_json()
        jawaban_siswa = data.get('jawaban_siswa', '').strip()
        kunci_jawaban = data.get('kunci_jawaban', '').strip()

        if not jawaban_siswa or not kunci_jawaban:
            return jsonify({'similarity_score': 0.0, 'method': 'empty_input'}), 200

        # Hitung TF-IDF dan Cosine Similarity
        corpus = [kunci_jawaban, jawaban_siswa]
        vectors = compute_tfidf(corpus)
        score = cosine_similarity(vectors[0], vectors[1])

        # Clamp between 0 and 1
        score = max(0.0, min(1.0, score))

        return jsonify({
            'similarity_score': round(score, 4),
            'method':           'tfidf_cosine_bahasa_indonesia',
            'kunci_words':      len(tokenize(kunci_jawaban)),
            'jawaban_words':    len(tokenize(jawaban_siswa)),
        })

    except Exception as e:
        return jsonify({'error': str(e), 'similarity_score': 0.0}), 500


# ============================================================
# Health check
# ============================================================
@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status':  'OK',
        'service': 'Assessment NLP + K-Means Microservice',
        'version': '2.0',
        'endpoints': ['/cluster', '/score-essay', '/health']
    })


if __name__ == '__main__':
    print("=" * 60)
    print("  🐍 Flask Microservice - SMP Katolik St. Johanis")
    print("  Endpoints:")
    print("    POST /cluster      → K-Means Clustering")
    print("    POST /score-essay  → NLP Essay Scoring")
    print("    GET  /health       → Status Check")
    print("=" * 60)
    app.run(host='0.0.0.0', port=5000, debug=True)
