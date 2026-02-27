/**
 * DOCUMENT SCANNER MODULE
 * CamScanner-like document scanning with:
 * - Camera capture (rear camera on mobile)
 * - Auto edge detection with manual corner adjustment
 * - Perspective correction (homography-based)
 * - Scanner filters (Color, Grayscale, B&W)
 * - Preview and save workflow
 */

const DocumentScanner = (function () {
    'use strict';

    // ==========================================
    // CONFIGURATION
    // ==========================================

    const CONFIG = {
        maxOutputSize: 2000,
        jpegQuality: 0.92,
        cornerHandleRadius: 16,
        cornerHandleColor: '#2196F3',
        cornerLineColor: '#2196F3',
        cornerLineWidth: 3,
        overlayColor: 'rgba(0, 0, 0, 0.4)',
        defaultMargin: 0.05,
        adaptiveBlurPercent: 0.08
    };

    // ==========================================
    // STATE
    // ==========================================

    let state = {
        step: 'idle',
        stream: null,
        capturedCanvas: null,
        corners: [],
        transformedCanvas: null,
        processedCanvas: null,
        filterMode: 'bw',
        dragCorner: -1,
        isDragging: false,
        documentId: null,
        documentType: null,
        onSaveCallback: null,
        cropScale: 1,
        cropOffsetX: 0,
        cropOffsetY: 0
    };

    let els = {};

    // ==========================================
    // INITIALIZE DOM REFERENCES
    // ==========================================

    function initDOM() {
        els = {
            modal: document.getElementById('scanner-modal'),
            // Camera step
            stepCamera: document.getElementById('scanner-step-camera'),
            video: document.getElementById('scanner-video'),
            btnCapture: document.getElementById('scanner-btn-capture'),
            btnCloseCamera: document.getElementById('scanner-close-camera'),
            cameraError: document.getElementById('scanner-camera-error'),
            // Crop step
            stepCrop: document.getElementById('scanner-step-crop'),
            cropCanvas: document.getElementById('scanner-crop-canvas'),
            btnRetakeCrop: document.getElementById('scanner-btn-retake-crop'),
            btnApplyCrop: document.getElementById('scanner-btn-apply-crop'),
            btnCloseC: document.getElementById('scanner-close-crop'),
            cropLoading: document.getElementById('scanner-crop-loading'),
            // Preview step
            stepPreview: document.getElementById('scanner-step-preview'),
            previewCanvas: document.getElementById('scanner-preview-canvas'),
            btnRetakePreview: document.getElementById('scanner-btn-retake-preview'),
            btnSave: document.getElementById('scanner-btn-save'),
            btnClosePreview: document.getElementById('scanner-close-preview'),
            filterButtons: document.querySelectorAll('.scanner-filter-btn'),
            previewLoading: document.getElementById('scanner-preview-loading'),
            saveLoading: document.getElementById('scanner-save-loading')
        };
    }

    // ==========================================
    // PUBLIC: OPEN SCANNER
    // ==========================================

    function open(documentId, documentType, onSave) {
        if (!els.modal) initDOM();

        state.documentId = documentId;
        state.documentType = documentType;
        state.onSaveCallback = onSave;
        state.filterMode = 'bw';

        els.modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        showStep('camera');
        startCamera();
        bindEvents();
    }

    // ==========================================
    // PUBLIC: CLOSE SCANNER
    // ==========================================

    function close() {
        stopCamera();
        state.step = 'idle';
        state.capturedCanvas = null;
        state.corners = [];
        state.transformedCanvas = null;
        state.processedCanvas = null;
        state.dragCorner = -1;
        state.isDragging = false;

        if (els.modal) {
            els.modal.classList.remove('active');
        }
        document.body.style.overflow = '';
        unbindEvents();
    }

    // ==========================================
    // STEP NAVIGATION
    // ==========================================

    function showStep(step) {
        state.step = step;
        els.stepCamera.style.display = step === 'camera' ? 'flex' : 'none';
        els.stepCrop.style.display = step === 'crop' ? 'flex' : 'none';
        els.stepPreview.style.display = step === 'preview' ? 'flex' : 'none';
    }

    // ==========================================
    // CAMERA
    // ==========================================

    async function startCamera() {
        els.cameraError.style.display = 'none';
        els.video.style.display = 'block';
        els.btnCapture.disabled = false;

        try {
            const constraints = {
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            };

            state.stream = await navigator.mediaDevices.getUserMedia(constraints);
            els.video.srcObject = state.stream;
            await els.video.play();
        } catch (err) {
            console.error('Camera error:', err);
            els.video.style.display = 'none';
            els.btnCapture.disabled = true;
            els.cameraError.style.display = 'flex';

            if (err.name === 'NotAllowedError') {
                els.cameraError.querySelector('p').textContent =
                    'Permiso de cámara denegado. Por favor habilite el acceso a la cámara en la configuración del navegador.';
            } else if (err.name === 'NotFoundError') {
                els.cameraError.querySelector('p').textContent =
                    'No se encontró ninguna cámara en este dispositivo.';
            } else {
                els.cameraError.querySelector('p').textContent =
                    'Error al acceder a la cámara: ' + err.message;
            }
        }
    }

    function stopCamera() {
        if (state.stream) {
            state.stream.getTracks().forEach(function (t) { t.stop(); });
            state.stream = null;
        }
        if (els.video) {
            els.video.srcObject = null;
        }
    }

    function capturePhoto() {
        if (!state.stream) return;

        const video = els.video;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        state.capturedCanvas = canvas;
        stopCamera();

        // Auto-detect corners
        state.corners = autoDetectCorners(canvas);

        showStep('crop');
        drawCropView();
    }

    // ==========================================
    // AUTO EDGE DETECTION
    // ==========================================

    function autoDetectCorners(canvas) {
        var w = canvas.width;
        var h = canvas.height;

        try {
            // Downscale for faster processing
            var maxDim = 400;
            var scale = Math.min(maxDim / w, maxDim / h, 1);
            var sw = Math.round(w * scale);
            var sh = Math.round(h * scale);

            var tmpCanvas = document.createElement('canvas');
            tmpCanvas.width = sw;
            tmpCanvas.height = sh;
            var tmpCtx = tmpCanvas.getContext('2d');
            tmpCtx.drawImage(canvas, 0, 0, sw, sh);
            var imgData = tmpCtx.getImageData(0, 0, sw, sh);
            var data = imgData.data;

            // Convert to grayscale
            var gray = new Float32Array(sw * sh);
            for (var i = 0; i < sw * sh; i++) {
                gray[i] = data[i * 4] * 0.299 + data[i * 4 + 1] * 0.587 + data[i * 4 + 2] * 0.114;
            }

            // Box blur (5x5)
            var blurred = boxBlurSeparable(gray, sw, sh, 2);

            // Sobel edge magnitude
            var edges = sobelMagnitude(blurred, sw, sh);

            // Find threshold (85th percentile)
            var sorted = Float32Array.from(edges);
            sorted.sort();
            var threshold = sorted[Math.floor(sorted.length * 0.85)];
            if (threshold < 20) threshold = 20;

            // Collect edge pixels in 4 regions
            var topPts = [], bottomPts = [], leftPts = [], rightPts = [];
            var marginX = Math.round(sw * 0.08);
            var marginY = Math.round(sh * 0.08);

            for (var y = marginY; y < sh - marginY; y++) {
                for (var x = marginX; x < sw - marginX; x++) {
                    if (edges[y * sw + x] > threshold) {
                        if (y < sh * 0.4) topPts.push({ x: x, y: y });
                        if (y > sh * 0.6) bottomPts.push({ x: x, y: y });
                        if (x < sw * 0.4) leftPts.push({ x: x, y: y });
                        if (x > sw * 0.6) rightPts.push({ x: x, y: y });
                    }
                }
            }

            // Fit lines: top/bottom as y = a*x + b, left/right as x = a*y + b
            var topLine = fitLineHorizontal(topPts);
            var bottomLine = fitLineHorizontal(bottomPts);
            var leftLine = fitLineVertical(leftPts);
            var rightLine = fitLineVertical(rightPts);

            if (topLine && bottomLine && leftLine && rightLine) {
                // Intersect to get 4 corners
                var tl = intersectHV(topLine, leftLine);
                var tr = intersectHV(topLine, rightLine);
                var br = intersectHV(bottomLine, rightLine);
                var bl = intersectHV(bottomLine, leftLine);

                if (tl && tr && br && bl) {
                    var corners = [
                        { x: tl.x / scale, y: tl.y / scale },
                        { x: tr.x / scale, y: tr.y / scale },
                        { x: br.x / scale, y: br.y / scale },
                        { x: bl.x / scale, y: bl.y / scale }
                    ];

                    // Validate corners are within image bounds (with margin)
                    var valid = corners.every(function (c) {
                        return c.x >= -w * 0.1 && c.x <= w * 1.1 && c.y >= -h * 0.1 && c.y <= h * 1.1;
                    });

                    // Validate corners form a reasonable quadrilateral
                    if (valid && isReasonableQuad(corners, w, h)) {
                        // Clamp to image bounds
                        return corners.map(function (c) {
                            return {
                                x: Math.max(0, Math.min(w - 1, c.x)),
                                y: Math.max(0, Math.min(h - 1, c.y))
                            };
                        });
                    }
                }
            }
        } catch (e) {
            console.warn('Auto-detection failed, using defaults:', e);
        }

        // Fallback: margin-based defaults
        var m = CONFIG.defaultMargin;
        return [
            { x: w * m, y: h * m },
            { x: w * (1 - m), y: h * m },
            { x: w * (1 - m), y: h * (1 - m) },
            { x: w * m, y: h * (1 - m) }
        ];
    }

    function isReasonableQuad(corners, w, h) {
        // The quad should cover at least 10% and at most 100% of image area
        var area = quadArea(corners);
        var imgArea = w * h;
        var ratio = area / imgArea;
        if (ratio < 0.1 || ratio > 1.0) return false;

        // All interior angles should be between 30 and 170 degrees
        for (var i = 0; i < 4; i++) {
            var a = corners[i];
            var b = corners[(i + 1) % 4];
            var c = corners[(i + 2) % 4];
            var angle = angleBetween(a, b, c);
            if (angle < 30 || angle > 170) return false;
        }

        return true;
    }

    function quadArea(pts) {
        // Shoelace formula
        var n = pts.length;
        var area = 0;
        for (var i = 0; i < n; i++) {
            var j = (i + 1) % n;
            area += pts[i].x * pts[j].y;
            area -= pts[j].x * pts[i].y;
        }
        return Math.abs(area) / 2;
    }

    function angleBetween(a, b, c) {
        var ba = { x: a.x - b.x, y: a.y - b.y };
        var bc = { x: c.x - b.x, y: c.y - b.y };
        var dot = ba.x * bc.x + ba.y * bc.y;
        var cross = ba.x * bc.y - ba.y * bc.x;
        return Math.abs(Math.atan2(cross, dot)) * (180 / Math.PI);
    }

    // ==========================================
    // LINE FITTING & INTERSECTION
    // ==========================================

    function fitLineHorizontal(points) {
        // Fit y = a*x + b using least squares
        if (points.length < 5) return null;

        // Use RANSAC-like approach: sample many pairs, find best fit
        var bestInliers = 0;
        var bestA = 0, bestB = 0;
        var iterations = Math.min(50, points.length);

        for (var iter = 0; iter < iterations; iter++) {
            var idx1 = Math.floor(Math.random() * points.length);
            var idx2 = Math.floor(Math.random() * points.length);
            if (idx1 === idx2) continue;

            var p1 = points[idx1];
            var p2 = points[idx2];
            if (Math.abs(p2.x - p1.x) < 1) continue;

            var a = (p2.y - p1.y) / (p2.x - p1.x);
            var b = p1.y - a * p1.x;
            var threshold = 5;

            var inliers = 0;
            for (var i = 0; i < points.length; i++) {
                var dist = Math.abs(points[i].y - (a * points[i].x + b));
                if (dist < threshold) inliers++;
            }

            if (inliers > bestInliers) {
                bestInliers = inliers;
                bestA = a;
                bestB = b;
            }
        }

        if (bestInliers < 5) return null;
        return { a: bestA, b: bestB };
    }

    function fitLineVertical(points) {
        // Fit x = a*y + b using least squares
        if (points.length < 5) return null;

        var bestInliers = 0;
        var bestA = 0, bestB = 0;
        var iterations = Math.min(50, points.length);

        for (var iter = 0; iter < iterations; iter++) {
            var idx1 = Math.floor(Math.random() * points.length);
            var idx2 = Math.floor(Math.random() * points.length);
            if (idx1 === idx2) continue;

            var p1 = points[idx1];
            var p2 = points[idx2];
            if (Math.abs(p2.y - p1.y) < 1) continue;

            var a = (p2.x - p1.x) / (p2.y - p1.y);
            var b = p1.x - a * p1.y;
            var threshold = 5;

            var inliers = 0;
            for (var i = 0; i < points.length; i++) {
                var dist = Math.abs(points[i].x - (a * points[i].y + b));
                if (dist < threshold) inliers++;
            }

            if (inliers > bestInliers) {
                bestInliers = inliers;
                bestA = a;
                bestB = b;
            }
        }

        if (bestInliers < 5) return null;
        return { a: bestA, b: bestB };
    }

    function intersectHV(hLine, vLine) {
        // hLine: y = a*x + b
        // vLine: x = a*y + b
        // Substitute: x = vLine.a * (hLine.a * x + hLine.b) + vLine.b
        // x = vLine.a * hLine.a * x + vLine.a * hLine.b + vLine.b
        // x (1 - vLine.a * hLine.a) = vLine.a * hLine.b + vLine.b
        var denom = 1 - vLine.a * hLine.a;
        if (Math.abs(denom) < 1e-10) return null;
        var x = (vLine.a * hLine.b + vLine.b) / denom;
        var y = hLine.a * x + hLine.b;
        return { x: x, y: y };
    }

    // ==========================================
    // IMAGE PROCESSING UTILITIES
    // ==========================================

    function boxBlurSeparable(gray, w, h, radius) {
        var temp = new Float32Array(w * h);
        var result = new Float32Array(w * h);

        // Horizontal pass
        for (var y = 0; y < h; y++) {
            var rowOff = y * w;
            var sum = 0;
            var left = 0;
            var right = Math.min(radius, w - 1);

            for (var i = left; i <= right; i++) {
                sum += gray[rowOff + i];
            }

            for (var x = 0; x < w; x++) {
                var curLeft = Math.max(0, x - radius);
                var curRight = Math.min(w - 1, x + radius);
                temp[rowOff + x] = sum / (curRight - curLeft + 1);

                // Slide window
                var addIdx = x + radius + 1;
                if (addIdx < w) sum += gray[rowOff + addIdx];
                var remIdx = x - radius;
                if (remIdx >= 0) sum -= gray[rowOff + remIdx];
            }
        }

        // Vertical pass
        for (var x = 0; x < w; x++) {
            var sum = 0;
            var top = 0;
            var bot = Math.min(radius, h - 1);

            for (var i = top; i <= bot; i++) {
                sum += temp[i * w + x];
            }

            for (var y = 0; y < h; y++) {
                var curTop = Math.max(0, y - radius);
                var curBot = Math.min(h - 1, y + radius);
                result[y * w + x] = sum / (curBot - curTop + 1);

                var addIdx = y + radius + 1;
                if (addIdx < h) sum += temp[addIdx * w + x];
                var remIdx = y - radius;
                if (remIdx >= 0) sum -= temp[remIdx * w + x];
            }
        }

        return result;
    }

    function sobelMagnitude(gray, w, h) {
        var mag = new Float32Array(w * h);

        for (var y = 1; y < h - 1; y++) {
            for (var x = 1; x < w - 1; x++) {
                var idx = y * w + x;
                // Sobel X
                var gx = -gray[(y - 1) * w + (x - 1)] + gray[(y - 1) * w + (x + 1)]
                       - 2 * gray[y * w + (x - 1)] + 2 * gray[y * w + (x + 1)]
                       - gray[(y + 1) * w + (x - 1)] + gray[(y + 1) * w + (x + 1)];
                // Sobel Y
                var gy = -gray[(y - 1) * w + (x - 1)] - 2 * gray[(y - 1) * w + x] - gray[(y - 1) * w + (x + 1)]
                       + gray[(y + 1) * w + (x - 1)] + 2 * gray[(y + 1) * w + x] + gray[(y + 1) * w + (x + 1)];

                mag[idx] = Math.sqrt(gx * gx + gy * gy);
            }
        }

        return mag;
    }

    // ==========================================
    // CROP VIEW (CORNER ADJUSTMENT)
    // ==========================================

    function drawCropView() {
        var canvas = els.cropCanvas;
        var container = canvas.parentElement;
        var containerW = container.clientWidth;
        var containerH = container.clientHeight;

        var imgW = state.capturedCanvas.width;
        var imgH = state.capturedCanvas.height;

        // Fit image in container
        var scale = Math.min(containerW / imgW, containerH / imgH, 1);
        var drawW = Math.round(imgW * scale);
        var drawH = Math.round(imgH * scale);

        canvas.width = containerW;
        canvas.height = containerH;

        state.cropScale = scale;
        state.cropOffsetX = Math.round((containerW - drawW) / 2);
        state.cropOffsetY = Math.round((containerH - drawH) / 2);

        var ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, containerW, containerH);

        // Draw captured image
        ctx.drawImage(state.capturedCanvas, state.cropOffsetX, state.cropOffsetY, drawW, drawH);

        // Draw overlay (darken outside selection)
        ctx.save();
        ctx.fillStyle = CONFIG.overlayColor;
        ctx.fillRect(0, 0, containerW, containerH);

        // Cut out the selection area
        var pts = state.corners.map(cornerToCanvas);
        ctx.beginPath();
        ctx.moveTo(pts[0].x, pts[0].y);
        for (var i = 1; i < pts.length; i++) {
            ctx.lineTo(pts[i].x, pts[i].y);
        }
        ctx.closePath();
        ctx.globalCompositeOperation = 'destination-out';
        ctx.fill();
        ctx.restore();

        // Draw selection lines
        ctx.strokeStyle = CONFIG.cornerLineColor;
        ctx.lineWidth = CONFIG.cornerLineWidth;
        ctx.setLineDash([]);
        ctx.beginPath();
        ctx.moveTo(pts[0].x, pts[0].y);
        for (var i = 1; i < pts.length; i++) {
            ctx.lineTo(pts[i].x, pts[i].y);
        }
        ctx.closePath();
        ctx.stroke();

        // Draw edge midpoint lines (subtle grid)
        ctx.strokeStyle = 'rgba(33, 150, 243, 0.3)';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);
        // Horizontal mid
        var midTop = { x: (pts[0].x + pts[1].x) / 2, y: (pts[0].y + pts[1].y) / 2 };
        var midBot = { x: (pts[3].x + pts[2].x) / 2, y: (pts[3].y + pts[2].y) / 2 };
        ctx.beginPath();
        ctx.moveTo(midTop.x, midTop.y);
        ctx.lineTo(midBot.x, midBot.y);
        ctx.stroke();
        // Vertical mid
        var midLeft = { x: (pts[0].x + pts[3].x) / 2, y: (pts[0].y + pts[3].y) / 2 };
        var midRight = { x: (pts[1].x + pts[2].x) / 2, y: (pts[1].y + pts[2].y) / 2 };
        ctx.beginPath();
        ctx.moveTo(midLeft.x, midLeft.y);
        ctx.lineTo(midRight.x, midRight.y);
        ctx.stroke();
        ctx.setLineDash([]);

        // Draw corner handles
        for (var i = 0; i < pts.length; i++) {
            var p = pts[i];
            // Outer circle (white border)
            ctx.beginPath();
            ctx.arc(p.x, p.y, CONFIG.cornerHandleRadius + 2, 0, Math.PI * 2);
            ctx.fillStyle = 'white';
            ctx.fill();
            // Inner circle
            ctx.beginPath();
            ctx.arc(p.x, p.y, CONFIG.cornerHandleRadius, 0, Math.PI * 2);
            ctx.fillStyle = CONFIG.cornerHandleColor;
            ctx.fill();
            // Corner label
            ctx.fillStyle = 'white';
            ctx.font = 'bold 11px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            var labels = ['TL', 'TR', 'BR', 'BL'];
            ctx.fillText(labels[i], p.x, p.y);
        }
    }

    function cornerToCanvas(corner) {
        return {
            x: corner.x * state.cropScale + state.cropOffsetX,
            y: corner.y * state.cropScale + state.cropOffsetY
        };
    }

    function canvasToCorner(cx, cy) {
        return {
            x: (cx - state.cropOffsetX) / state.cropScale,
            y: (cy - state.cropOffsetY) / state.cropScale
        };
    }

    function getEventPos(e) {
        var rect = els.cropCanvas.getBoundingClientRect();
        if (e.touches && e.touches.length > 0) {
            return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
        }
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    function findClosestCorner(pos) {
        var minDist = Infinity;
        var minIdx = -1;
        var pts = state.corners.map(cornerToCanvas);
        for (var i = 0; i < pts.length; i++) {
            var dx = pos.x - pts[i].x;
            var dy = pos.y - pts[i].y;
            var dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < minDist && dist < CONFIG.cornerHandleRadius * 2.5) {
                minDist = dist;
                minIdx = i;
            }
        }
        return minIdx;
    }

    // Crop mouse/touch handlers
    function onCropPointerDown(e) {
        e.preventDefault();
        var pos = getEventPos(e);
        var idx = findClosestCorner(pos);
        if (idx >= 0) {
            state.isDragging = true;
            state.dragCorner = idx;
            els.cropCanvas.style.cursor = 'grabbing';
        }
    }

    function onCropPointerMove(e) {
        e.preventDefault();
        if (state.isDragging && state.dragCorner >= 0) {
            var pos = getEventPos(e);
            var corner = canvasToCorner(pos.x, pos.y);
            // Clamp to image bounds
            corner.x = Math.max(0, Math.min(state.capturedCanvas.width - 1, corner.x));
            corner.y = Math.max(0, Math.min(state.capturedCanvas.height - 1, corner.y));
            state.corners[state.dragCorner] = corner;
            drawCropView();
        } else {
            // Update cursor based on proximity to corners
            var pos = getEventPos(e);
            var idx = findClosestCorner(pos);
            els.cropCanvas.style.cursor = idx >= 0 ? 'grab' : 'default';
        }
    }

    function onCropPointerUp(e) {
        state.isDragging = false;
        state.dragCorner = -1;
        els.cropCanvas.style.cursor = 'default';
    }

    // ==========================================
    // PERSPECTIVE TRANSFORM
    // ==========================================

    function applyCropTransform() {
        els.cropLoading.style.display = 'flex';
        els.btnApplyCrop.disabled = true;

        // Use requestAnimationFrame to let the UI update before heavy processing
        requestAnimationFrame(function () {
            setTimeout(function () {
                try {
                    var corners = orderCorners(state.corners);

                    // Compute output dimensions
                    var widthTop = dist(corners[0], corners[1]);
                    var widthBot = dist(corners[3], corners[2]);
                    var heightLeft = dist(corners[0], corners[3]);
                    var heightRight = dist(corners[1], corners[2]);

                    var outW = Math.round(Math.max(widthTop, widthBot));
                    var outH = Math.round(Math.max(heightLeft, heightRight));

                    // Cap to max size
                    var maxScale = Math.min(CONFIG.maxOutputSize / outW, CONFIG.maxOutputSize / outH, 1);
                    outW = Math.round(outW * maxScale);
                    outH = Math.round(outH * maxScale);

                    // Ensure minimum dimensions
                    outW = Math.max(outW, 100);
                    outH = Math.max(outH, 100);

                    state.transformedCanvas = perspectiveTransform(state.capturedCanvas, corners, outW, outH);

                    // Apply current filter
                    applyFilter(state.filterMode);

                    showStep('preview');
                    drawPreview();
                } catch (err) {
                    console.error('Transform error:', err);
                    alert('Error al procesar la imagen. Intente ajustar las esquinas.');
                }

                els.cropLoading.style.display = 'none';
                els.btnApplyCrop.disabled = false;
            }, 50);
        });
    }

    function orderCorners(corners) {
        // Order: top-left, top-right, bottom-right, bottom-left
        // Find centroid
        var cx = 0, cy = 0;
        corners.forEach(function (c) { cx += c.x; cy += c.y; });
        cx /= 4; cy /= 4;

        // Classify each corner by its position relative to centroid
        var tl = null, tr = null, br = null, bl = null;
        corners.forEach(function (c) {
            if (c.x <= cx && c.y <= cy) tl = c;
            else if (c.x > cx && c.y <= cy) tr = c;
            else if (c.x > cx && c.y > cy) br = c;
            else bl = c;
        });

        // Fallback if classification fails
        if (!tl || !tr || !br || !bl) {
            var sorted = corners.slice().sort(function (a, b) { return (a.x + a.y) - (b.x + b.y); });
            tl = sorted[0];
            br = sorted[3];
            if (sorted[1].x > sorted[2].x) {
                tr = sorted[1]; bl = sorted[2];
            } else {
                tr = sorted[2]; bl = sorted[1];
            }
        }

        return [tl, tr, br, bl];
    }

    function dist(a, b) {
        var dx = a.x - b.x;
        var dy = a.y - b.y;
        return Math.sqrt(dx * dx + dy * dy);
    }

    function perspectiveTransform(srcCanvas, corners, outW, outH) {
        // Source points (the 4 corners in the captured image)
        var src = corners;

        // Destination points (output rectangle)
        var dst = [
            { x: 0, y: 0 },
            { x: outW - 1, y: 0 },
            { x: outW - 1, y: outH - 1 },
            { x: 0, y: outH - 1 }
        ];

        // Compute inverse homography: dst -> src
        var H = computeHomography(dst, src);
        if (!H) {
            // Fallback: just return a cropped version
            var outCanvas = document.createElement('canvas');
            outCanvas.width = outW;
            outCanvas.height = outH;
            var outCtx = outCanvas.getContext('2d');
            outCtx.drawImage(srcCanvas, 0, 0, outW, outH);
            return outCanvas;
        }

        var outCanvas = document.createElement('canvas');
        outCanvas.width = outW;
        outCanvas.height = outH;
        var outCtx = outCanvas.getContext('2d');

        var srcCtx = srcCanvas.getContext('2d');
        var srcData = srcCtx.getImageData(0, 0, srcCanvas.width, srcCanvas.height);
        var outImgData = outCtx.createImageData(outW, outH);
        var srcPixels = srcData.data;
        var outPixels = outImgData.data;
        var srcW = srcCanvas.width;
        var srcH = srcCanvas.height;

        for (var y = 0; y < outH; y++) {
            for (var x = 0; x < outW; x++) {
                // Apply homography
                var w = H[6] * x + H[7] * y + H[8];
                var sx = (H[0] * x + H[1] * y + H[2]) / w;
                var sy = (H[3] * x + H[4] * y + H[5]) / w;

                // Bilinear interpolation
                var outIdx = (y * outW + x) * 4;

                if (sx >= 0 && sx < srcW - 1 && sy >= 0 && sy < srcH - 1) {
                    var x0 = Math.floor(sx);
                    var y0 = Math.floor(sy);
                    var x1 = x0 + 1;
                    var y1 = y0 + 1;
                    var fx = sx - x0;
                    var fy = sy - y0;

                    var i00 = (y0 * srcW + x0) * 4;
                    var i10 = (y0 * srcW + x1) * 4;
                    var i01 = (y1 * srcW + x0) * 4;
                    var i11 = (y1 * srcW + x1) * 4;

                    var w00 = (1 - fx) * (1 - fy);
                    var w10 = fx * (1 - fy);
                    var w01 = (1 - fx) * fy;
                    var w11 = fx * fy;

                    outPixels[outIdx] = Math.round(srcPixels[i00] * w00 + srcPixels[i10] * w10 + srcPixels[i01] * w01 + srcPixels[i11] * w11);
                    outPixels[outIdx + 1] = Math.round(srcPixels[i00 + 1] * w00 + srcPixels[i10 + 1] * w10 + srcPixels[i01 + 1] * w01 + srcPixels[i11 + 1] * w11);
                    outPixels[outIdx + 2] = Math.round(srcPixels[i00 + 2] * w00 + srcPixels[i10 + 2] * w10 + srcPixels[i01 + 2] * w01 + srcPixels[i11 + 2] * w11);
                    outPixels[outIdx + 3] = 255;
                } else {
                    // Out of bounds: white
                    outPixels[outIdx] = 255;
                    outPixels[outIdx + 1] = 255;
                    outPixels[outIdx + 2] = 255;
                    outPixels[outIdx + 3] = 255;
                }
            }
        }

        outCtx.putImageData(outImgData, 0, 0);
        return outCanvas;
    }

    function computeHomography(src, dst) {
        // Set up 8x8 linear system for h[0..7], with h[8]=1
        // For each point pair: dst_i = H * src_i
        // h11*xs + h12*ys + h13 - xd*h31*xs - xd*h32*ys = xd  (h33=1)
        // h21*xs + h22*ys + h23 - yd*h31*xs - yd*h32*ys = yd

        var A = [];
        var b = [];

        for (var i = 0; i < 4; i++) {
            var xs = src[i].x, ys = src[i].y;
            var xd = dst[i].x, yd = dst[i].y;

            A.push([xs, ys, 1, 0, 0, 0, -xd * xs, -xd * ys]);
            b.push(xd);

            A.push([0, 0, 0, xs, ys, 1, -yd * xs, -yd * ys]);
            b.push(yd);
        }

        var h = solveLinearSystem8(A, b);
        if (!h) return null;

        return [h[0], h[1], h[2], h[3], h[4], h[5], h[6], h[7], 1];
    }

    function solveLinearSystem8(A, b) {
        var n = 8;
        // Augmented matrix
        var M = [];
        for (var i = 0; i < n; i++) {
            M[i] = A[i].slice();
            M[i].push(b[i]);
        }

        // Forward elimination with partial pivoting
        for (var col = 0; col < n; col++) {
            var maxRow = col;
            var maxVal = Math.abs(M[col][col]);
            for (var row = col + 1; row < n; row++) {
                if (Math.abs(M[row][col]) > maxVal) {
                    maxVal = Math.abs(M[row][col]);
                    maxRow = row;
                }
            }

            if (maxVal < 1e-12) return null; // Singular

            // Swap
            if (maxRow !== col) {
                var tmp = M[col];
                M[col] = M[maxRow];
                M[maxRow] = tmp;
            }

            // Eliminate below
            var pivot = M[col][col];
            for (var row = col + 1; row < n; row++) {
                var factor = M[row][col] / pivot;
                for (var j = col; j <= n; j++) {
                    M[row][j] -= factor * M[col][j];
                }
            }
        }

        // Back substitution
        var x = new Array(n);
        for (var i = n - 1; i >= 0; i--) {
            var sum = M[i][n];
            for (var j = i + 1; j < n; j++) {
                sum -= M[i][j] * x[j];
            }
            x[i] = sum / M[i][i];
        }

        return x;
    }

    // ==========================================
    // SCANNER FILTERS
    // ==========================================

    function applyFilter(mode) {
        if (!state.transformedCanvas) return;

        state.filterMode = mode;

        var srcCanvas = state.transformedCanvas;
        var w = srcCanvas.width;
        var h = srcCanvas.height;

        var outCanvas = document.createElement('canvas');
        outCanvas.width = w;
        outCanvas.height = h;
        var outCtx = outCanvas.getContext('2d');

        // Copy original
        outCtx.drawImage(srcCanvas, 0, 0);
        var imgData = outCtx.getImageData(0, 0, w, h);
        var data = imgData.data;

        if (mode === 'color') {
            applyColorEnhance(data, w, h);
        } else if (mode === 'gray') {
            applyGrayscaleEnhance(data, w, h);
        } else {
            applyBWScanner(data, w, h);
        }

        outCtx.putImageData(imgData, 0, 0);
        state.processedCanvas = outCanvas;
    }

    function applyColorEnhance(data, w, h) {
        // Increase contrast, remove shadows, brighten
        // Step 1: Compute grayscale for local normalization
        var gray = new Float32Array(w * h);
        for (var i = 0; i < w * h; i++) {
            gray[i] = data[i * 4] * 0.299 + data[i * 4 + 1] * 0.587 + data[i * 4 + 2] * 0.114;
        }

        // Step 2: Large blur for background estimation
        var radius = Math.max(Math.round(w * CONFIG.adaptiveBlurPercent), 5);
        var bg = boxBlurSeparable(gray, w, h, radius);

        // Step 3: Normalize and enhance
        for (var i = 0; i < w * h; i++) {
            var bgVal = Math.max(bg[i], 1);
            var scale = 220 / bgVal;
            scale = Math.min(scale, 3.0);

            var idx = i * 4;
            data[idx] = clamp(data[idx] * scale + 10);
            data[idx + 1] = clamp(data[idx + 1] * scale + 10);
            data[idx + 2] = clamp(data[idx + 2] * scale + 10);
        }

        // Step 4: Increase saturation slightly
        for (var i = 0; i < w * h; i++) {
            var idx = i * 4;
            var r = data[idx], g = data[idx + 1], b = data[idx + 2];
            var avg = (r + g + b) / 3;
            var sat = 1.3;
            data[idx] = clamp(avg + (r - avg) * sat);
            data[idx + 1] = clamp(avg + (g - avg) * sat);
            data[idx + 2] = clamp(avg + (b - avg) * sat);
        }
    }

    function applyGrayscaleEnhance(data, w, h) {
        // Convert to grayscale and enhance
        var gray = new Float32Array(w * h);
        for (var i = 0; i < w * h; i++) {
            gray[i] = data[i * 4] * 0.299 + data[i * 4 + 1] * 0.587 + data[i * 4 + 2] * 0.114;
        }

        // Background estimation
        var radius = Math.max(Math.round(w * CONFIG.adaptiveBlurPercent), 5);
        var bg = boxBlurSeparable(gray, w, h, radius);

        for (var i = 0; i < w * h; i++) {
            var bgVal = Math.max(bg[i], 1);
            var normalized = (gray[i] / bgVal) * 220 + 15;

            // Increase contrast
            normalized = (normalized - 128) * 1.6 + 128;
            var val = clamp(normalized);

            var idx = i * 4;
            data[idx] = val;
            data[idx + 1] = val;
            data[idx + 2] = val;
        }
    }

    function applyBWScanner(data, w, h) {
        // Adaptive thresholding for clean B&W scanner look
        var gray = new Float32Array(w * h);
        for (var i = 0; i < w * h; i++) {
            gray[i] = data[i * 4] * 0.299 + data[i * 4 + 1] * 0.587 + data[i * 4 + 2] * 0.114;
        }

        // Large blur for local background
        var radius = Math.max(Math.round(w * CONFIG.adaptiveBlurPercent), 5);
        var bg = boxBlurSeparable(gray, w, h, radius);

        // Adaptive threshold with offset
        var offset = 8;
        for (var i = 0; i < w * h; i++) {
            var val = (gray[i] > bg[i] - offset) ? 255 : 0;
            var idx = i * 4;
            data[idx] = val;
            data[idx + 1] = val;
            data[idx + 2] = val;
        }

        // Light cleanup: remove isolated noise pixels (simple 3x3 median-like)
        var cleaned = new Uint8Array(w * h);
        for (var y = 1; y < h - 1; y++) {
            for (var x = 1; x < w - 1; x++) {
                var idx = y * w + x;
                var center = data[idx * 4];
                var count = 0;

                for (var dy = -1; dy <= 1; dy++) {
                    for (var dx = -1; dx <= 1; dx++) {
                        if (data[((y + dy) * w + (x + dx)) * 4] === center) count++;
                    }
                }

                cleaned[idx] = (count >= 5) ? center : (255 - center);
            }
        }

        // Apply cleaned values
        for (var y = 1; y < h - 1; y++) {
            for (var x = 1; x < w - 1; x++) {
                var idx = (y * w + x) * 4;
                var val = cleaned[y * w + x];
                data[idx] = val;
                data[idx + 1] = val;
                data[idx + 2] = val;
            }
        }
    }

    function clamp(val) {
        return Math.max(0, Math.min(255, Math.round(val)));
    }

    // ==========================================
    // PREVIEW
    // ==========================================

    function drawPreview() {
        if (!state.processedCanvas) return;

        var canvas = els.previewCanvas;
        var container = canvas.parentElement;
        var containerW = container.clientWidth;
        var containerH = container.clientHeight;

        var imgW = state.processedCanvas.width;
        var imgH = state.processedCanvas.height;

        var scale = Math.min(containerW / imgW, containerH / imgH, 1);
        var drawW = Math.round(imgW * scale);
        var drawH = Math.round(imgH * scale);

        canvas.width = containerW;
        canvas.height = containerH;

        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#f0f0f0';
        ctx.fillRect(0, 0, containerW, containerH);

        // Draw shadow
        var ox = Math.round((containerW - drawW) / 2);
        var oy = Math.round((containerH - drawH) / 2);
        ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
        ctx.shadowBlur = 20;
        ctx.shadowOffsetX = 5;
        ctx.shadowOffsetY = 5;
        ctx.fillStyle = 'white';
        ctx.fillRect(ox, oy, drawW, drawH);
        ctx.shadowColor = 'transparent';

        ctx.drawImage(state.processedCanvas, ox, oy, drawW, drawH);

        // Update filter buttons
        els.filterButtons.forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.filter === state.filterMode);
        });
    }

    function changeFilter(mode) {
        els.previewLoading.style.display = 'flex';

        requestAnimationFrame(function () {
            setTimeout(function () {
                applyFilter(mode);
                drawPreview();
                els.previewLoading.style.display = 'none';
            }, 50);
        });
    }

    // ==========================================
    // SAVE SCANNED IMAGE
    // ==========================================

    function saveScannedImage() {
        if (!state.processedCanvas || !state.documentId) return;

        els.btnSave.disabled = true;
        els.saveLoading.style.display = 'flex';
        els.btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        state.processedCanvas.toBlob(function (blob) {
            if (!blob) {
                alert('Error al generar la imagen.');
                els.btnSave.disabled = false;
                els.saveLoading.style.display = 'none';
                els.btnSave.innerHTML = '<i class="fas fa-save"></i> Guardar';
                return;
            }

            var timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
            var fileName = 'scanned-doc-' + timestamp + '.jpg';
            var file = new File([blob], fileName, { type: 'image/jpeg' });

            var formData = new FormData();
            formData.append('file', file);
            formData.append('document_id', state.documentId);
            formData.append('document_type', state.documentType || 'Contract');
            formData.append('file_type', 'scanned');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'controllers/upload_attachment.php', true);

            xhr.onload = function () {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Notify parent
                        if (typeof state.onSaveCallback === 'function') {
                            state.onSaveCallback(response);
                        }
                        close();
                    } else {
                        alert('Error: ' + (response.error || 'No se pudo guardar'));
                    }
                } catch (err) {
                    alert('Error del servidor al guardar la imagen.');
                }
                els.btnSave.disabled = false;
                els.saveLoading.style.display = 'none';
                els.btnSave.innerHTML = '<i class="fas fa-save"></i> Guardar';
            };

            xhr.onerror = function () {
                alert('Error de red. Intente nuevamente.');
                els.btnSave.disabled = false;
                els.saveLoading.style.display = 'none';
                els.btnSave.innerHTML = '<i class="fas fa-save"></i> Guardar';
            };

            xhr.send(formData);
        }, 'image/jpeg', CONFIG.jpegQuality);
    }

    // ==========================================
    // EVENT BINDING
    // ==========================================

    var boundHandlers = {};

    function bindEvents() {
        // Camera events
        boundHandlers.capture = function () { capturePhoto(); };
        boundHandlers.closeCamera = function () { close(); };
        els.btnCapture.addEventListener('click', boundHandlers.capture);
        els.btnCloseCamera.addEventListener('click', boundHandlers.closeCamera);

        // Crop events (mouse)
        boundHandlers.cropDown = onCropPointerDown;
        boundHandlers.cropMove = onCropPointerMove;
        boundHandlers.cropUp = onCropPointerUp;
        els.cropCanvas.addEventListener('mousedown', boundHandlers.cropDown);
        els.cropCanvas.addEventListener('mousemove', boundHandlers.cropMove);
        els.cropCanvas.addEventListener('mouseup', boundHandlers.cropUp);
        els.cropCanvas.addEventListener('mouseleave', boundHandlers.cropUp);
        // Crop events (touch)
        els.cropCanvas.addEventListener('touchstart', boundHandlers.cropDown, { passive: false });
        els.cropCanvas.addEventListener('touchmove', boundHandlers.cropMove, { passive: false });
        els.cropCanvas.addEventListener('touchend', boundHandlers.cropUp);

        boundHandlers.retakeCrop = function () { showStep('camera'); startCamera(); };
        boundHandlers.applyCrop = function () { applyCropTransform(); };
        boundHandlers.closeCrop = function () { close(); };
        els.btnRetakeCrop.addEventListener('click', boundHandlers.retakeCrop);
        els.btnApplyCrop.addEventListener('click', boundHandlers.applyCrop);
        els.btnCloseC.addEventListener('click', boundHandlers.closeCrop);

        // Preview events
        boundHandlers.retakePreview = function () { showStep('camera'); startCamera(); };
        boundHandlers.save = function () { saveScannedImage(); };
        boundHandlers.closePreview = function () { close(); };
        els.btnRetakePreview.addEventListener('click', boundHandlers.retakePreview);
        els.btnSave.addEventListener('click', boundHandlers.save);
        els.btnClosePreview.addEventListener('click', boundHandlers.closePreview);

        // Filter buttons
        els.filterButtons.forEach(function (btn) {
            var handler = function () { changeFilter(btn.dataset.filter); };
            btn._handler = handler;
            btn.addEventListener('click', handler);
        });

        // Resize handler for crop canvas
        boundHandlers.resize = function () {
            if (state.step === 'crop') drawCropView();
            else if (state.step === 'preview') drawPreview();
        };
        window.addEventListener('resize', boundHandlers.resize);

        // Escape key to close
        boundHandlers.keydown = function (e) {
            if (e.key === 'Escape') close();
        };
        document.addEventListener('keydown', boundHandlers.keydown);
    }

    function unbindEvents() {
        if (els.btnCapture) {
            els.btnCapture.removeEventListener('click', boundHandlers.capture);
            els.btnCloseCamera.removeEventListener('click', boundHandlers.closeCamera);
        }
        if (els.cropCanvas) {
            els.cropCanvas.removeEventListener('mousedown', boundHandlers.cropDown);
            els.cropCanvas.removeEventListener('mousemove', boundHandlers.cropMove);
            els.cropCanvas.removeEventListener('mouseup', boundHandlers.cropUp);
            els.cropCanvas.removeEventListener('mouseleave', boundHandlers.cropUp);
            els.cropCanvas.removeEventListener('touchstart', boundHandlers.cropDown);
            els.cropCanvas.removeEventListener('touchmove', boundHandlers.cropMove);
            els.cropCanvas.removeEventListener('touchend', boundHandlers.cropUp);
        }
        if (els.btnRetakeCrop) {
            els.btnRetakeCrop.removeEventListener('click', boundHandlers.retakeCrop);
            els.btnApplyCrop.removeEventListener('click', boundHandlers.applyCrop);
            els.btnCloseC.removeEventListener('click', boundHandlers.closeCrop);
        }
        if (els.btnRetakePreview) {
            els.btnRetakePreview.removeEventListener('click', boundHandlers.retakePreview);
            els.btnSave.removeEventListener('click', boundHandlers.save);
            els.btnClosePreview.removeEventListener('click', boundHandlers.closePreview);
        }
        if (els.filterButtons) {
            els.filterButtons.forEach(function (btn) {
                if (btn._handler) btn.removeEventListener('click', btn._handler);
            });
        }
        window.removeEventListener('resize', boundHandlers.resize);
        document.removeEventListener('keydown', boundHandlers.keydown);
        boundHandlers = {};
    }

    // ==========================================
    // PUBLIC API
    // ==========================================

    return {
        open: open,
        close: close
    };

})();
