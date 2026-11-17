{{-- Render studio: one three.js scene filling the window, photographed by scripts/render-studio.mjs. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Render studio</title>
    <style>
        html, body { margin: 0; height: 100%; overflow: hidden; background: #07080f; }
        canvas { display: block; width: 100vw; height: 100vh; }
    </style>
</head>
<body>
    <canvas id="studioCanvas"></canvas>
    <script type="application/json" id="studioData">@json($data)</script>
    @vite('resources/js/studio/index.js')
</body>
</html>
