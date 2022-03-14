const VISUAL_CIRCLE_RADIUS = 40;
const FULL_DASH_ARRAY = Math.floor(2 * Math.PI * VISUAL_CIRCLE_RADIUS);
const WARNING_THRESHOLD = 3600;
const ALERT_THRESHOLD = 300;
const UNIT_MILLISECONDS = 1000;

const TIMER_SETTING_JQUERY_INDEXES = [
    ':eq(0), :eq(1)', ':eq(2), :eq(3)', ':eq(4), :eq(5)'
]

const TIMER_KERNEL_HTML = `<div class="base-timer__digits_container">
<div class="time_part back"><span class="base-timer__digit_label bigTime">8
   </span><span class="base-timer__digit_label bigTime">8
   </span><span class="base-timer__colon_label">:
   </span><span class="base-timer__digit_label bigTime">8
   </span><span class="base-timer__digit_label bigTime">8
   </span><span class="base-timer__colon_label">&nbsp;
   </span><span class="base-timer__digit_label smallTime">8
   </span><span class="base-timer__digit_label smallTime">8
   </span>
</div>
<div class="time_part fore"><span id="base-timer-label0" class="base-timer__digit_label bigTime">
   </span><span id="base-timer-label1" class="base-timer__digit_label bigTime">
   </span><span class="base-timer__colon_label">:
   </span><span id="base-timer-label2" class="base-timer__digit_label bigTime">
   </span><span id="base-timer-label3" class="base-timer__digit_label bigTime">
   </span><span class="base-timer__colon_label">&nbsp;
   </span><span id="base-timer-label4" class="base-timer__digit_label smallTime">
   </span><span id="base-timer-label5" class="base-timer__digit_label smallTime">
   </span></div>
</div>`;

const VISUAL_PATH_HTML = `
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" class="base-timer__svg" viewBox="0 0 100 100"
   ><g class="base-timer__circle"
   ><circle class="base-timer__path-elapsed" cx="50" cy="50" r="${VISUAL_CIRCLE_RADIUS}"
   ></circle><path
    id="base-timer-path-remaining"
    stroke-dasharray="${FULL_DASH_ARRAY}"
    class="base-timer__path-remaining"
    d="
      M 50, 50
      m -40, 0
      a 40,40 0 1,0 80,0
      a 40,40 0 1,0 -80,0
    "></path></g></svg>`;

const KEYPAD_SHELL_HTML = `<table class="keypad" cellpadding="5" cellspacing="3">
</table>`;

class Timer {
    constructor(vertexId, serverId) {
        this.id = vertexId;
        this.vertexId = vertexId;
        this.serverId = serverId;
        
        this.timerShell = null;
        this.timeLeft = 0;
        this.timeLimit = 0;
        this.digitVal = -1;
        this.onSet = {state:false};
        this.descriptionViewer = null;

        this.timerShellHtml = $(`<div class="base-timer unselectable" id="timer${this.id}"></div>`);
        this.timerKernelHtml = $(TIMER_KERNEL_HTML);

        this.vertexVisualizer = null;
        this.roundPathBox = null;
        this.digitViewer = null;
        this.remainingPath = null;
        this.isBlinking = false;
    } // constructor

    makeKeypad( setButton ) {
        if( this.timerShell == null ) {
            return;
        }
        const onSet = this.onSet;
        onSet.state = true;
        let timeBuffer = 0;
        let BufferDigits = [0,0,0];
        let settingIndex = 0; // 0 : hours, 1 : minutes, 2 : seconds
        const fgDigits = this.fgDigits;
        const descView = this.descriptionViewer;
        changeTime();
        changeIndex();
        const keypadShell = $(KEYPAD_SHELL_HTML).appendTo(this.timerShell);

        let trList = [];
        for( let i = 0 ; i < 4; i++ ) {
            trList[i] = $('<tr></tr>').appendTo(keypadShell);
        }
        
        for( let i = 0; i < 9; i++ ) {
            $(`<td>${i+1}</td>`).appendTo(trList[ Math.floor(i / 3) ]).on('click', function() {
                addCode( i+1 );
            })
        }

        
        const unLinkButton = $(`<td class="keypad_red"><i class="material-icons">close</i></td>`).appendTo(trList[3]);
        unLinkButton.unbind();
        unLinkButton.on('click',function(){
            closeKeypad();
            descView.html('DB unlinking ...');
            updateCollection(`${this.serverId}vertex`, `${this.vertexId}`, null, function () {
                descView.html('DB unlinked');
                setTimeout(function(){ 
                    onSet.state = false;
                }, 1000);
            }, function (e) {
                onSet.state = false; 
                console.log('error delete db',e);
                alert('연결 해제 실패');
            });
        }.bind(this));

        $(`<td>0</td>`).appendTo(trList[3]).on('click', function() {
            addCode( 0 );
        });
        
        const startButton = $(`<td class="keypad_green"><i class="material-icons">play_arrow</i></td>`).appendTo(trList[3]);
        startButton.unbind();
        startButton.on('click', function () {
            closeKeypad();
            descView.html('update DB ...');
            updateCollection(`${this.serverId}vertex`, `${this.vertexId}`, {
                deadline: (Math.floor(Date.now() / UNIT_MILLISECONDS) + timeBuffer ),
                timeLimit: 32400
            }, function () {
                descView.html('update complete');
                setTimeout(function(){ 
                    onSet.state = false;
                }, 1000);
            }, function (e) {
                onSet.state = false;
                console.log('error update db',e);
                alert('업데이트 실패');
            });
        }.bind(this));

        const closeButton = $(`<button class="timerSetButton"><i class="material-icons">keyboard_arrow_up</i></button>`).appendTo(this.timerShell);
        closeButton.unbind();
        closeButton.on('click', function() {
            closeKeypad();
            descView.html('');
            onSet.state = false;
        });
        

        function closeKeypad() {
            fgDigits.unbind();
            fgDigits.html('');
            keypadShell.empty();
            keypadShell.remove();
            closeButton.unbind();
            closeButton.remove();
            setButton.removeAttr('hidden');            
            fgDigits.removeClass("blinking");
        }

        function addCode( digit ) {
            // 0 : * 60^2, 1 : * 60^1, 2 : 60^0


            const currUnit = Math.pow(60, 2 - settingIndex);
            const currBuff =  settingIndex == 0 ? Math.floor(timeBuffer / currUnit) :
            Math.floor(timeBuffer % (currUnit * 60) / currUnit); // ( 83*60^2 + 30 * 60 + 1 ) % 60^3 / 60^2 => 83
            BufferDigits[settingIndex]++;

            if( BufferDigits[settingIndex] > 1 ) {
                const addValue = currBuff % 10 * 10 + digit;
                timeBuffer -= currBuff * currUnit;
                timeBuffer += addValue * currUnit;
                BufferDigits[settingIndex] = 0;
                settingIndex = (settingIndex + 1) % 3;
                changeIndex();
            } else {
                timeBuffer -= currBuff * currUnit;
                timeBuffer += digit * currUnit;
            }

            timeBuffer %= 360000;

            changeTime();
        }

        function changeTime() {
            const timeFormat = Timer.formatTime(timeBuffer);
            fgDigits.each(function( i ) {
                this.innerHTML = timeFormat[i];
            });
            if( timeBuffer > 0 ) {
            let d = new Date( Date.now() + timeBuffer*1000 );
               descView.html(`${d.getMonth()+1}/${d.getDate()} ${d.getHours()}:${d.getMinutes()} 소환 예정`);
            } else {
                descView.html('');
            }
        }

        function changeIndex() {
            BufferDigits[settingIndex] = 0;
           fgDigits.removeClass("blinking");
           fgDigits.filter(TIMER_SETTING_JQUERY_INDEXES[settingIndex]).addClass("blinking"); 
        }

        for( let i = 0; i < 3; i++ ) {
            const constI = i;
            const filteredDigits = fgDigits.filter(TIMER_SETTING_JQUERY_INDEXES[i]);
            filteredDigits.on('click',function() {
                if( constI != settingIndex ) {
                    settingIndex = constI;
                    changeIndex();
                }
            });
        }
    } // keypadFunction

    detach() {
        if( this.fgDigits ) {
            this.fgDigits.unbind();
            this.fgDigits.removeClass("blinking");
            this.fgDigits.html('');
        }
        
        this.onSet.state = false;
        this.timerShell.empty();
        this.timerShell.remove();
        this.descriptionViewer = null;
        this.timerShell = null;
        console.log(`timer${this.id} detached`);
    }

    attachTo(target) {
        if (this.timerShell != null) {
            this.detach();
        }
        this.timerShell = this.timerShellHtml.appendTo(target);
        this.descriptionViewer = $(`<span class="descView"></span>`).appendTo(this.timerShell);

        const timerKernel = this.timerKernelHtml.appendTo(this.timerShell);
        const hoursSelector = timerKernel.find("#hoursSelector");
        hoursSelector.empty();
        for (let i = 9; i >= 0; i--) {
            hoursSelector.append(`<option value="${i * 3600}">${i}</option>`);
        }


        this.fgDigits = timerKernel.find(`span[id*=base-timer-label]`);
        
        const timerSetButton = $(`<button class="timerSetButton"><i class="material-icons">keyboard_arrow_down</i></button>`).appendTo(this.timerShell);
        timerSetButton.on('click',function() {
            timerSetButton.attr('hidden',true);
            this.makeKeypad( timerSetButton );
        }.bind(this));
        console.log(`timer${this.id} attached`);
    }

    syncAction(currentTime) {
        if (currentTime == undefined) { // init action
            currentTime = Math.floor(Date.now() / UNIT_MILLISECONDS);
            this.digitVal = -1;
            if( this.vertexVisualizer != null && this.isBlinking ) {
                this.roundPathBox.removeClass('blinking');
                this.isBlinking = false;
            }
        }
        this.timeLeft = this.deadline - currentTime;
        const leftTime = Math.max(this.timeLeft, 0);

        if (leftTime >= 0) {

            if( this.vertexVisualizer != null ) {
                this.setCircleDasharray();
                if( leftTime <= WARNING_THRESHOLD && !this.isBlinking) {
                    this.roundPathBox.addClass('blinking');
                    this.isBlinking = true;
                }
                const hoursVal = Math.floor(leftTime / 3600) + 1;
                if (this.digitVal != hoursVal) {
                    this.digitVal = hoursVal;
                    this.digitViewer.html(hoursVal);
                }
            }
            
            if ( !this.onSet.state && this.timerShell != null) {
                const timeFormat = Timer.formatTime(leftTime);
                this.fgDigits.each(function( i ) {
                    this.innerHTML = timeFormat[i];
                });

                if( timeFormat[5] % 3 == 0 ) {
                    const d = new Date(this.deadline*1000);
                    this.descriptionViewer.html(`${d.getMonth()+1}/${d.getDate()} ${d.getHours()}:${d.getMinutes()} 소환`);
                }
            }
        }

        return leftTime;
    }

    static formatTime(time) {
        const hours = Math.floor(time / 3600);
        const minutes = Math.floor(time % 3600 / 60);
        const seconds = time % 60;

        return [ 
            Math.floor(hours / 10),
            hours % 10,
            Math.floor(minutes / 10),
            minutes % 10,
            Math.floor(seconds / 10),
            seconds % 10
        ];
    }

    calculateTimeFraction() {
        const rawTimeFraction = (this.timeLeft % 3600) / 3600;
        return rawTimeFraction - (1 / 3600) * (1 - rawTimeFraction);
    }

    setCircleDasharray() {
        const circleDasharray = `${(
            this.calculateTimeFraction() * FULL_DASH_ARRAY
        ).toFixed(0)} ${FULL_DASH_ARRAY}`;
        this.remainingPath.attr("stroke-dasharray", circleDasharray);
    }

    makeVisualizer(container, isHidden, color) {
        const vertexVisualizer = $(
            `<div ${isHidden ? 'hidden' : ''} class="vertexVisualizer"></div>`).appendTo(container);
        const roundPathBox = $(VISUAL_PATH_HTML).appendTo(vertexVisualizer);
        this.remainingPath = roundPathBox.find('#base-timer-path-remaining');
        this.remainingPath.css({color:color});
        this.digitViewer = $(`<span class="vertexDigit"></span>`).appendTo(vertexVisualizer);
        this.roundPathBox = roundPathBox;
        return this.vertexVisualizer = vertexVisualizer;
    }

    timeup() {
        if (this.vertexVisualizer != null) {
            this.digitViewer.html('');
            this.remainingPath.attr("stroke-dasharray", FULL_DASH_ARRAY);
        }
    }

    removeVisualizer() {
        if (this.vertexVisualizer != null) {
            this.vertexVisualizer.empty();
            this.vertexVisualizer.remove();
            this.vertexVisualizer = null;
            this.digitViewer = null;
            this.remainingPath = null;
        }
    }

} // Timer class



