<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="NhuBeBii" content="width=device-width, initial-scale=1.0">
    <title>iu NhuBebii</title>
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <canvas id="Tinh_iu_mau_tim"></canvas>
    <script >
        var settings = {
            HatPts: {
                length: 500, 
                duration: 3, 
                velocity: 300, 
                effect: -0.75, 
                size: 70, 

            },

        };

        function Point() 
        {
            function Point(i, j) 
            {
                this.x = (i !== 'null') ? i : 0;
                this.y = (j !== 'null') ? j : 0;
            }
            Point.prototype.clone = function () 
            {
                return new Point(this.x, this.y);
            };
            Point.prototype.length = function (length) 
            {
                if (typeof length == 'undefined')
                    return Math.sqrt(this.x * this.x + this.y * this.y);
                this.normalize();
                this.x *= length;
                this.y *= length;
                return this;
            };
            Point.prototype.normalize = function () 
            {
                var length = this.length();
                this.x /= length;
                this.y /= length;
                return this;
            };
            return Point;
        };
        var Point = Point();
      
        function HPhanTu() 
        {
            function HatPt() 
            {
                this.position = new Point();
                this.velocity = new Point();
                this.acceleration = new Point();
                this.age = 0;
            }
            HatPt.prototype.initialize = function (x, y, dx, dy) 
            {
                this.position.x = x;
                this.position.y = y;
                this.velocity.x = dx;
                this.velocity.y = dy;
                this.acceleration.x = dx * settings.HatPts.effect;
                this.acceleration.y = dy * settings.HatPts.effect;
                this.age = 0;
            };
            HatPt.prototype.update = function (deltaTime) 
            {
                this.position.x += this.velocity.x * deltaTime;
                this.position.y += this.velocity.y * deltaTime;
                this.velocity.x += this.acceleration.x * deltaTime;
                this.velocity.y += this.acceleration.y * deltaTime;
                this.age += deltaTime;
            };
            HatPt.prototype.draw = function (vb, image) 
            {
                function ease(t) 
                {
                    return (--t) * t * t + 1;
                }
                var size = image.width * ease(this.age / settings.HatPts.duration);
                vb.globalAlpha = 1 - this.age / settings.HatPts.duration;
                vb.drawImage(image, this.position.x - size / 2, this.position.y - size / 2, size, size);
            };
            return HatPt;
        };
        var HatPt = HPhanTu();

        var HPhanTuChuyenDong = (function () 
        {
            var HatPts,
                firstActive = 0,
                firstFree = 0,
                duration = settings.HatPts.duration;
            function HPhanTuChuyenDong(length) 
            {
                HatPts = new Array(length);
                for (var i = 0; i < HatPts.length; i++)
                    HatPts[i] = new HatPt();
            }
            HPhanTuChuyenDong.prototype.add = function (x, y, dx, dy) 
            {
                HatPts[firstFree].initialize(x, y, dx, dy);
                firstFree++;
                if (firstFree == HatPts.length) firstFree = 0;
                if (firstActive == firstFree) firstActive++;
                if (firstActive == HatPts.length) firstActive = 0;
            };
            HPhanTuChuyenDong.prototype.update = function (deltaTime) 
            {
                var i;
                if (firstActive < firstFree) 
                {
                    for (i = firstActive; i < firstFree; i++)
                        HatPts[i].update(deltaTime);
                }
                if (firstFree < firstActive) 
                {
                    for (i = firstActive; i < HatPts.length; i++)
                        HatPts[i].update(deltaTime);
                    for (i = 0; i < firstFree; i++)
                        HatPts[i].update(deltaTime);
                }
                while (HatPts[firstActive].age >= duration && firstActive != firstFree) 
                {
                    firstActive++;
                    if (firstActive == HatPts.length) firstActive = 0;
                }
            };

            HPhanTuChuyenDong.prototype.draw = function (vb, image) 
            {
                if (firstActive < firstFree) {
                    for (i = firstActive; i < firstFree; i++)
                        HatPts[i].draw(vb, image);
                }
                if (firstFree < firstActive) {
                    for (i = firstActive; i < HatPts.length; i++)
                        HatPts[i].draw(vb, image);
                    for (i = 0; i < firstFree; i++)
                        HatPts[i].draw(vb, image);
                }
            };
            return HPhanTuChuyenDong;
        })();
       
        (function (canvas) {
            var vb = canvas.getContext('2d'),
                HatPts = new HPhanTuChuyenDong(settings.HatPts.length),
                HatPtRate = settings.HatPts.length / settings.HatPts.duration,
                time;
            function TraiTim(t) 
            {
                return new Point(
                    160 * Math.pow(Math.sin(t), 3),
                    130 * Math.cos(t) - 50 * Math.cos(2 * t) - 20 * Math.cos(3 * t) - 10 * Math.cos(4 * t) + 25
                );
            }
        
            var image = (function () {
                var canvas = document.createElement('canvas'),
                vb = canvas.getContext('2d');
                canvas.width = settings.HatPts.size;
                canvas.height = settings.HatPts.size;
                function to(t) 
                {
                    var Point = TraiTim(t);
                    Point.x = settings.HatPts.size / 2 + Point.x * settings.HatPts.size / 350;
                    Point.y = settings.HatPts.size / 2 - Point.y * settings.HatPts.size / 350;
                    return Point;
                }
                vb.beginPath();
                var t = -Math.PI;
                var Point = to(t);
                vb.moveTo(Point.x, Point.y);
                while (t < Math.PI) 
                {
                    t += 0.01;
                    Point = to(t);
                    vb.lineTo(Point.x, Point.y);
                }
                vb.closePath();
                vb.fillStyle = '#EE00EE';
                vb.fill();
                var image = new Image();
                image.src = canvas.toDataURL();
                return image;
            })();
            
            function render() 
            {
                requestAnimationFrame(render);
                var newTime = new Date().getTime() / 1000,
                    deltaTime = newTime - (time || newTime);
                time = newTime;
                vb.clearRect(0, 0, canvas.width, canvas.height);
                var amount = HatPtRate * deltaTime;
                for (var i = 0; i < amount; i++) {
                    var pos = TraiTim(Math.PI - 2 * Math.PI * Math.random());
                    var dir = pos.clone().length(settings.HatPts.velocity);
                    HatPts.add(canvas.width / 2 + pos.x, canvas.height / 2 - pos.y, dir.x, -dir.y);
                }
                HatPts.update(deltaTime);
                HatPts.draw(vb, image);
            }
            function onResize() {
                canvas.width = canvas.clientWidth;
                canvas.height = canvas.clientHeight;
            }
            window.onresize = onResize;
            setTimeout(function () {
                onResize();
                render();
            }, 10);
        })(document.getElementById('Tinh_iu_mau_tim'));
    </script>
    
</body>
</html>
