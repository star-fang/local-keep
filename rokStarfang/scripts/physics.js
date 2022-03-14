class PhysicsEngine {
    

    constructor(target, onMove, pos) {
        if( pos ) {
            target.css( {
                top: pos.top,
                left: pos.left
            });
            this.pos = pos;
        } else {
            this.pos = { top: 0, left: 0 };
        }
        
        this.target = target;
        this.scale = { x: 1, y: 1 };
        this.onMove = onMove;
        console.log(`PhysicsEngine of ${target.prop("id")} contructed`);
    }

    activateZoomModule(size, actingPoint) {
        let touchDistance = 0
        let scale_before = { x: 1, y: 1 };
        const target = this.target;

        if (actingPoint == undefined) {
            actingPoint = target;
        }


        actingPoint.unbind("mousewheel");
        actingPoint.on("mousewheel", function (e) {
            var delta = e.delta || e.originalEvent.wheelDelta;
            if (delta === undefined) {
                delta = e.originalEvent.detail;
            }
            delta = Math.max(-1, Math.min(1, delta)) // cap the delta to [-1,1] for cross browser consistency

            zoom(  this.pos, this.scale, this.onMove
                , 0.1, delta
                , PhysicsEngine.getCursorPosInTarget(target, e, this.scale));
            return false;
        }.bind(this));

        actingPoint.unbind("touchstart.pez");
        actingPoint.on("touchstart.pez", function (e) {
            actingPoint.unbind("mousewheel");
            e = e || window.event;
            if (e.touches.length >= 2) {
                actingPoint.unbind("touchend");
                actingPoint.unbind("touchmove");
                touchDistance = Math.pow((e.touches[0].pageX - e.touches[1].pageX), 2) + Math.pow((e.touches[0].pageY - e.touches[1].pageY), 2);
                actingPoint.on("touchmove", doubleTouchMove.bind(this));
                actingPoint.on("touchend", doubleTouchEnd.bind(this));
                return false;
            }
        }.bind(this));

        function doubleTouchMove(e) {

            const curTouchDistance = Math.pow((e.touches[0].pageX - e.touches[1].pageX), 2) + Math.pow((e.touches[0].pageY - e.touches[1].pageY), 2);
            zoom(  this.pos, this.scale, this.onMove
                , 0.1, curTouchDistance - touchDistance > 0 ? 1 : -1
                , PhysicsEngine.getCursorPosInTarget(target, e, this.scale));

            touchDistance = curTouchDistance;
            return false;
        }

        function doubleTouchEnd(e) {
            actingPoint.unbind("touchmove");
            actingPoint.unbind("touchend");
        }

        function zoom( pos, scale, onMove, factor, delta, zoomPos) {
            const bgWidth = actingPoint.innerWidth();
            const bgHeight = actingPoint.innerHeight();
            const landscapeMode = bgWidth > bgHeight; // else : portrait mode
            const min_scale = landscapeMode ? bgHeight / size.height : bgWidth / size.width;
            const max_scale = landscapeMode ? 3 * bgWidth / size.width : 3 * bgHeight / size.height;

            if ((scale.x === min_scale && delta < 0)
                || (scale.x === max_scale) && delta > 0) return;

                scale.x += delta * factor * scale.x;
                scale.x = Math.max(min_scale, Math.min(max_scale, scale.x));
                scale.y = scale.x;

             pos.top = target.position().top - zoomPos.y * (scale.y - scale_before.y);
             pos.left = target.position().left - zoomPos.x * (scale.x - scale_before.x);
            PhysicsEngine.scaleTarget( target, pos, scale, onMove );

            scale_before.x = scale.x;
            scale_before.y = scale.y;
        } // zoom

    } // zoom module

    static calcVelocity(dx, dy, dt) {
        if (dt > 0) {
            return {
                speed: 1000 * Math.sqrt(dx * dx + dy * dy) / dt,
                direction: Math.atan2(dy, dx)
            }
        }
        return null;
    }

    activateDraggingModule(actionPoint, cursorAction) {
        const CLICK_TIME_CRITERIA = 200; // ms
        const MEASURE_TIME_UNIT = 20; // ms
        const INERTIA_TIME_UNIT = 20; // (m)ms
        //http://www.adequatelygood.com/Minimum-Timer-Intervals-in-JavaScript.html
        const DECELERATION = 2; // (n)px / (m)ms^2
        const target = this.target;
        const parent = target.parent();
        let mCurPosX, mCurPosY;
        let dx, dy, dt; // velocity
        let inertiaAction = null; // interval
        let ePosX = 0, ePosY = 0;
        let measureStartTime = 0;

        if (!actionPoint) {
            actionPoint = target;
        }

        actionPoint.unbind("mousedown");
        actionPoint.unbind("touchstart.ped");
        actionPoint.unbind("mousemove");
        actionPoint.unbind("mouseup");
        actionPoint.unbind("touchend");
        actionPoint.unbind("touchmove");
        actionPoint.unbind("mouseleave");
        if (cursorAction) {
            actionPoint.on("mousemove.move", function (e) {
                cursorAction(PhysicsEngine.getCursorPosInTarget(target, e, this.scale), false);
            }.bind(this));
        }
        actionPoint.on("mousedown", function (e) {
            e = e || window.event;
            ePosX = e.clientX;
            ePosY = e.clientY;
            actionPoint.on("mouseup", mousingEnd.bind(this));
            actionPoint.on("mouseleave.drag", mousingEnd.bind(this));
            actionPoint.on("mousemove.drag", mouseDrag.bind(this));

            startMeasure();

        }.bind(this));

        actionPoint.on("touchstart.ped", function (e) {
            e = e || window.event;
            
            actionPoint.unbind("mousedown");
            actionPoint.unbind("mouseup");
            actionPoint.unbind("mouseleave");
            actionPoint.unbind("mousemove");

            if (e.touches.length == 1) {
                ePosX = e.touches[0].clientX;
                ePosY = e.touches[0].clientY;
                actionPoint.on("touchmove", singleTouchMove.bind(this));
                actionPoint.on("touchend", singleTouchEnd.bind(this));

                startMeasure();
            }
        }.bind(this));

        function startMeasure() {

            if (inertiaAction != null) {
                clearInterval(inertiaAction);
                inertiaAction = null;
                dx = 0, dy = 0, dt = 0;
            }

            measureStartTime = performance.now();
            let mTime = measureStartTime;
            let mPrePosX = ePosX;
            let mPrePosY = ePosY;
            mCurPosX = mPrePosX;
            mCurPosY = mPrePosY;

            inertiaAction = setInterval(() => {
                dx = mCurPosX - mPrePosX;
                dy = mCurPosY - mPrePosY;

                const curTime = performance.now();
                dt = curTime - mTime;
                mTime = curTime;
                
                mPrePosX = mCurPosX;
                mPrePosY = mCurPosY;
            }, MEASURE_TIME_UNIT);
        }

        function mouseDrag(e) {

                mCurPosX = e.clientX;
                mCurPosY = e.clientY;
                const targetOffset = target.offset();
                const parentOffset = parent.offset();
                this.pos.top = targetOffset.top - parentOffset.top + mCurPosY - ePosY;
                this.pos.left = targetOffset.left - parentOffset.left + mCurPosX - ePosX;

                PhysicsEngine.moveTarget( target, this.pos, this.onMove );

                ePosX = mCurPosX;
                ePosY = mCurPosY;
            
            return false;

        }


        function mousingEnd(e) {
            actionPoint.unbind("mouseup");
            actionPoint.unbind("mousemove.drag");
            actionPoint.unbind("mouseleave.drag");

    
            dragEnd(this.pos, this.scale, this.onMove, e);
        }

        function singleTouchMove(e) {
                mCurPosX = e.touches[0].clientX;
                mCurPosY = e.touches[0].clientY;

                const targetOffset = target.offset();
                const parentOffset = parent.offset();
                this.pos.top = targetOffset.top + mCurPosY - ePosY - parentOffset.top;
                this.pos.left = targetOffset.left + mCurPosX - ePosX - parentOffset.left;

                PhysicsEngine.moveTarget( target, this.pos, this.onMove );

                ePosX = mCurPosX;
                ePosY = mCurPosY;
            
            return false;

        }

        function singleTouchEnd(e) {
            actionPoint.unbind("touchmove");
            actionPoint.unbind("touchend");
            dragEnd( this.pos, this.scale, this.onMove, e);
        }

        function dragEnd( pos, scale, onMove, e) {
            if (inertiaAction != null) {
                clearInterval(inertiaAction);
                inertiaAction = null;
            }

            if (dt > 0 && (dx != 0 || dy != 0)) {
                const targetOffset = target.offset();
                const parentOffset = parent.offset();
                pos.top = targetOffset.top - parentOffset.top;
                pos.left = targetOffset.left - parentOffset.left;
                inertiaAction = setInterval(() => {

                    pos.top += dy;
                    pos.left += dx;
                    PhysicsEngine.moveTarget( target, pos, onMove );

                    let velocity = PhysicsEngine.calcVelocity(dx, dy, dt);
                    const accX = DECELERATION * Math.cos(velocity.direction);
                    if (Math.abs(dx) < Math.abs(accX)) {
                        dx = 0;
                    } else {
                        dx -= accX;
                    }

                    const accY = DECELERATION * Math.sin(velocity.direction);
                    if (Math.abs(dy) < Math.abs(accY)) {
                        dy = 0;
                    } else {
                        dy -= accY;
                    }


                    if (dx == 0 && dy == 0) {
                        clearInterval(inertiaAction);
                        inertiaAction = null, dx = 0, dy = 0, dt = 0, velocity = null;
                    }

                }, INERTIA_TIME_UNIT);
            } else {
                const dragTime = performance.now() - measureStartTime;
                if ( cursorAction && dragTime <= CLICK_TIME_CRITERIA) {
                   cursorAction(PhysicsEngine.getCursorPosInTarget(target, e, scale), true);
                }
            }
        }
    } // activate parent dragging module

    static getCursorPosInTarget(target, evt, scale) {
        evt = evt || window.event;
        const rect = target[0].getBoundingClientRect();

        if (evt.touches) {
            if (evt.touches.length == 2) {
                const touches = evt.changedTouches;
                return {
                    x: (touches[0].clientX + touches[1].clientX - 2 * rect.left) / 2 / scale.x,
                    y: (touches[0].clientY + touches[1].clientY - 2 * rect.top) / 2 / scale.y
                };
            }
            const touch = evt.changedTouches[0];
            return {
                x: (touch.clientX - rect.left) / scale.x,
                y: (touch.clientY - rect.top) / scale.y
            };
        } else {
            return {
                x: (evt.clientX - rect.left) / scale.x,
                y: (evt.clientY - rect.top) / scale.y
            };
        }

    } // getCursorPosInTarget

    static async moveTarget( target, pos, onMove ) {
        onMove();
        target.css({
            left: `${pos.left}px`,
            top: `${pos.top}px`
        });
    }

    static async scaleTarget( target, pos, scale, onMove ) {
        onMove();
        target.css({
            left: `${pos.left}px`,
            top: `${pos.top}px`,
            transform: `scale(${scale.x},${scale.y})`
        });
    }

  
}