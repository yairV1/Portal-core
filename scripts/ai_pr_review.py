#!/usr/bin/env python3
"""
Revisión de Pull Requests con varios "agentes" de IA (Gemini),
cada uno con un rol distinto, pensado para el proyecto Portal-core
(PHP puro, estructura tipo MVC).

Cada revisor recibe el mismo diff del PR y comenta por su cuenta,
sin ver lo que dicen los demás — así obtienes varias perspectivas
independientes en vez de una sola opinión mezclada.
"""
import os
import sys
import time
import subprocess
import requests

GEMINI_API_KEY = os.environ["GEMINI_API_KEY"]
GITHUB_TOKEN = os.environ["GITHUB_TOKEN"]
REPO = os.environ["GITHUB_REPOSITORY"]  # ej: yairV1/Portal-core
PR_NUMBER = os.environ["PR_NUMBER"]
BASE_SHA = os.environ["BASE_SHA"]
HEAD_SHA = os.environ["HEAD_SHA"]

MODEL = "gemini-3.8-flash"
GEMINI_URL = f"https://generativelanguage.googleapis.com/v1beta/models/{MODEL}:generateContent"

MAX_DIFF_CHARS = 15000  # para no pasarnos de tokens / costo en PRs muy grandes
SLEEP_BETWEEN_CALLS = 15  # segundos, para no chocar con el límite del tier gratuito

REVIEWERS = [
    {
        "nombre": "Seguridad",
        "prompt": (
            "Eres un especialista en seguridad de aplicaciones web PHP. "
            "Revisa el siguiente diff de un Pull Request buscando SOLO problemas de seguridad: "
            "inyección SQL (consultas sin preparar), XSS, CSRF, credenciales o rutas de .env "
            "expuestas, subida de archivos sin validar, contraseñas en texto plano, control de "
            "acceso roto o sesiones mal manejadas. Si no encuentras nada grave dilo claramente, "
            "no inventes problemas. Responde en español, en viñetas cortas, citando archivo y "
            "línea cuando puedas."
        ),
    },
    {
        "nombre": "Arquitectura y estructura",
        "prompt": (
            "Eres un arquitecto de software revisando un proyecto PHP puro con estructura tipo "
            "MVC (app/Controllers, config/, routes/, public/, database/) y un router "
            "centralizado. Revisa el diff buscando SOLO problemas de estructura: código fuera de "
            "su módulo, lógica de negocio mezclada con la vista, duplicación evitable, nombres de "
            "archivos con tildes o espacios, o rutas no registradas correctamente en el router. "
            "Si todo está bien organizado, dilo. Responde en español, en viñetas cortas y "
            "concretas."
        ),
    },
    {
        "nombre": "Buenas prácticas PHP",
        "prompt": (
            "Eres un desarrollador PHP senior. Revisa el diff buscando SOLO buenas prácticas de "
            "código: uso correcto de PDO/mysqli con parámetros preparados, manejo de errores, "
            "nombres de variables/funciones claros, código repetido, y estilo consistente. "
            "Responde en español, en viñetas cortas."
        ),
    },
]


def run(cmd):
    return subprocess.run(
        cmd, shell=True, capture_output=True, text=True, check=True
    ).stdout


def get_diff():
    diff = run(f"git diff {BASE_SHA} {HEAD_SHA}")
    if len(diff) > MAX_DIFF_CHARS:
        diff = diff[:MAX_DIFF_CHARS] + "\n\n[...diff truncado por longitud...]"
    return diff


def call_gemini(system_prompt, diff):
    payload = {
        "contents": [
            {
                "role": "user",
                "parts": [
                    {"text": f"{system_prompt}\n\nDiff del PR:\n```diff\n{diff}\n```"}
                ],
            }
        ]
    }
    resp = requests.post(
        GEMINI_URL,
        headers={
            "x-goog-api-key": GEMINI_API_KEY,
            "Content-Type": "application/json",
        },
        json=payload,
        timeout=60,
    )
    resp.raise_for_status()
    data = resp.json()
    return data["candidates"][0]["content"]["parts"][0]["text"]


def post_comment(nombre, texto):
    url = f"https://api.github.com/repos/{REPO}/issues/{PR_NUMBER}/comments"
    body = f"### 🤖 Revisión IA — {nombre}\n\n{texto}"
    resp = requests.post(
        url,
        headers={
            "Authorization": f"Bearer {GITHUB_TOKEN}",
            "Accept": "application/vnd.github+json",
        },
        json={"body": body},
        timeout=30,
    )
    resp.raise_for_status()


def main():
    diff = get_diff()
    if not diff.strip():
        print("No hay cambios en el diff, no se revisa nada.")
        return

    for reviewer in REVIEWERS:
        print(f"Revisando con: {reviewer['nombre']}...")
        try:
            texto = call_gemini(reviewer["prompt"], diff)
            post_comment(reviewer["nombre"], texto)
        except Exception as e:
            print(f"Error con el revisor {reviewer['nombre']}: {e}", file=sys.stderr)
        time.sleep(SLEEP_BETWEEN_CALLS)


if __name__ == "__main__":
    main()
