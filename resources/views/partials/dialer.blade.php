<!-- IN-APP SOFTPHONE DIALER WIDGET -->
<div x-data="crmDialer()" 
     x-init="initDialer()" 
     @open-dialer.window="handleOpenDialer($event.detail)" 
     class="relative z-50">
    
    <!-- Floating Bottom-Right Launcher Button -->
    <div class="fixed bottom-6 right-6 flex items-center gap-2 no-print">
        <button type="button" 
                @click="toggleDialer()" 
                :class="isCalling ? 'bg-rose-600 hover:bg-rose-500 animate-pulse text-white shadow-rose-600/40' : 'bg-teal-700 hover:bg-teal-600 text-white shadow-teal-700/30'"
                class="w-13 h-13 rounded-full shadow-2xl flex items-center justify-center cursor-pointer transition-transform transform hover:scale-105 border-2 border-white/20"
                title="Open Phone Dialer">
            <template x-if="!isCalling">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </template>
            <template x-if="isCalling">
                <div class="flex items-center justify-center text-xs font-bold font-mono">
                    <span x-text="formatTimer(callTimer)"></span>
                </div>
            </template>
        </button>
    </div>

    <!-- Softphone Dialog Window -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-95"
         class="fixed bottom-22 right-6 w-84 sm:w-90 bg-slate-900 text-white rounded-2xl shadow-2xl border border-slate-800 overflow-hidden no-print"
         style="display: none;">

        <!-- Header -->
        <div class="px-4 py-3 bg-slate-950 border-b border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full" :class="isCalling ? 'bg-emerald-500 animate-ping' : 'bg-teal-500'"></div>
                <span class="text-xs font-bold tracking-wide uppercase text-slate-300">Tele-Calling Softphone</span>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" @click="isOpen = false" class="text-slate-400 hover:text-white p-1 rounded hover:bg-slate-800 transition text-sm leading-none">&times;</button>
            </div>
        </div>

        <!-- Contact Auto-Match Header Banner -->
        <div x-show="contactMatch" class="px-3.5 py-2 bg-teal-950/70 border-b border-teal-800/40 text-xs flex items-center justify-between">
            <div>
                <div class="font-bold text-teal-300 truncate max-w-[190px]" x-text="contactMatch?.name"></div>
                <div class="text-[10px] text-slate-400">
                    <span x-text="contactMatch?.city"></span>
                    <span x-show="contactMatch?.last_call_outcome" x-text="' • Last: ' + contactMatch?.last_call_outcome"></span>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase" :class="contactMatch?.type === 'customer' ? 'bg-teal-900 text-teal-200 border border-teal-700' : 'bg-amber-900 text-amber-200 border border-amber-700'" x-text="contactMatch?.badge"></span>
        </div>

        <!-- Phone Number Input & Display -->
        <div class="p-3 bg-slate-900 border-b border-slate-800">
            <div class="flex items-center justify-between bg-slate-950 rounded-xl px-3 py-2 border border-slate-800">
                <input type="text" 
                       x-model="phoneNumber" 
                       @input="onPhoneInput()" 
                       placeholder="Enter phone number..." 
                       :disabled="isCalling"
                       class="bg-transparent border-0 text-lg font-mono font-bold text-white focus:outline-hidden w-full tracking-wider">
                <div class="flex items-center gap-1">
                    <button type="button" x-show="phoneNumber.length > 0 && !isCalling" @click="backspace()" class="text-slate-400 hover:text-rose-400 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414A2 2 0 0110.828 5H19a2 2 0 012 2v10a2 2 0 01-2 2h-8.172a2 2 0 01-1.414-.586L3 12z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Call Screen -->
        <div x-show="isCalling" class="p-5 text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full bg-teal-500/20 border border-teal-500/30 flex items-center justify-center animate-pulse">
                <svg class="w-8 h-8 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>

            <div>
                <div class="text-xs uppercase tracking-widest text-slate-400 font-semibold" x-text="callStateText"></div>
                <div class="text-xl font-bold font-mono text-white mt-1" x-text="contactMatch?.name || phoneNumber"></div>
                <div class="text-2xl font-mono font-bold text-teal-400 mt-2" x-text="formatTimer(callTimer)"></div>
            </div>

            <!-- Call Controls -->
            <div class="flex items-center justify-center gap-4 pt-2">
                <button type="button" @click="toggleMute()" :class="isMuted ? 'bg-amber-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'" class="p-3 rounded-full transition cursor-pointer" title="Mute Microphone">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                </button>

                <button type="button" @click="hangUpCall()" class="bg-rose-600 hover:bg-rose-500 text-white px-6 py-3 rounded-full font-bold text-xs flex items-center gap-2 shadow-lg shadow-rose-600/30 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/></svg>
                    <span>End Call</span>
                </button>
            </div>
        </div>

        <!-- Post-Call Wrap-up Screen -->
        <div x-show="showWrapUp" class="p-4 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <span class="text-xs font-bold text-slate-300 uppercase">Call Wrap-up & Log</span>
                <span class="text-xs font-mono text-teal-400 font-bold" x-text="'Duration: ' + formatTimer(callTimer)"></span>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Call Outcome *</label>
                <select x-model="callOutcome" class="w-full bg-slate-950 text-white rounded-lg p-2 text-xs border border-slate-800 focus:border-teal-500 focus:outline-hidden font-bold">
                    <option value="Connected">Connected & Spoke</option>
                    <option value="Order Taken">Order Taken / Sold</option>
                    <option value="Interested">Interested / Follow-up</option>
                    <option value="Follow-up Required">Follow-up Required</option>
                    <option value="Not Interested">Not Interested</option>
                    <option value="Busy">Line Busy</option>
                    <option value="No Answer">No Answer / Ringing</option>
                    <option value="Wrong Number">Wrong Number</option>
                    <option value="Complaint">Customer Complaint</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Call Notes & Summary</label>
                <textarea x-model="callNotes" rows="2" placeholder="Discussion notes..." class="w-full bg-slate-950 text-white rounded-lg p-2 text-xs border border-slate-800 focus:border-teal-500 focus:outline-hidden"></textarea>
            </div>

            <div class="flex items-center justify-between pt-1">
                <button type="button" @click="cancelWrapUp()" class="text-xs text-slate-400 hover:text-white">Discard</button>
                <button type="button" @click="saveCallRecord()" :disabled="isSavingCall" class="btn bg-teal-600 hover:bg-teal-500 text-white text-xs px-4 py-1.5 font-bold rounded-lg shadow cursor-pointer">
                    <span x-text="isSavingCall ? 'Saving...' : 'Save Call to CRM'"></span>
                </button>
            </div>
        </div>

        <!-- Numeric Keypad (Idle Screen) -->
        <div x-show="!isCalling && !showWrapUp" class="p-3 space-y-3">
            <div class="grid grid-cols-3 gap-2">
                <template x-for="btn in keypadButtons" :key="btn.key">
                    <button type="button" 
                            @click="pressKey(btn.key)" 
                            class="bg-slate-800/80 hover:bg-slate-700/80 active:bg-teal-700/60 p-2.5 rounded-xl text-center border border-slate-700/50 transition cursor-pointer select-none">
                        <div class="text-base font-bold font-mono text-white leading-none" x-text="btn.key"></div>
                        <div class="text-[9px] text-slate-400 font-semibold mt-0.5" x-text="btn.sub"></div>
                    </button>
                </template>
            </div>

            <!-- Call Button -->
            <button type="button" 
                    @click="initiateCall()" 
                    :disabled="phoneNumber.trim().length < 5"
                    :class="phoneNumber.trim().length >= 5 ? 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/30' : 'bg-slate-800 text-slate-500 cursor-not-allowed'"
                    class="w-full py-2.5 rounded-xl font-bold text-sm text-white flex items-center justify-center gap-2 shadow-lg transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span>Start Call</span>
            </button>
        </div>
    </div>
</div>

<script>
function crmDialer() {
    return {
        isOpen: false,
        phoneNumber: '',
        contactMatch: null,
        isCalling: false,
        callStateText: 'Dialing...',
        callTimer: 0,
        timerInterval: null,
        isMuted: false,
        showWrapUp: false,
        callOutcome: 'Connected',
        callNotes: '',
        isSavingCall: false,
        audioCtx: null,

        keypadButtons: [
            { key: '1', sub: ' ' },
            { key: '2', sub: 'ABC' },
            { key: '3', sub: 'DEF' },
            { key: '4', sub: 'GHI' },
            { key: '5', sub: 'JKL' },
            { key: '6', sub: 'MNO' },
            { key: '7', sub: 'PQRS' },
            { key: '8', sub: 'TUV' },
            { key: '9', sub: 'WXYZ' },
            { key: '*', sub: ' ' },
            { key: '0', sub: '+' },
            { key: '#', sub: ' ' },
        ],

        initDialer() {
            // Setup Web Audio API Context lazily on first user interaction
        },

        playDtmfTone(freq1 = 697, freq2 = 1209) {
            try {
                if (!this.audioCtx) {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (AudioContext) this.audioCtx = new AudioContext();
                }
                if (!this.audioCtx) return;
                const osc1 = this.audioCtx.createOscillator();
                const osc2 = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                gain.gain.value = 0.08;
                osc1.frequency.value = freq1;
                osc2.frequency.value = freq2;
                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(this.audioCtx.destination);
                osc1.start();
                osc2.start();
                setTimeout(() => {
                    osc1.stop();
                    osc2.stop();
                }, 100);
            } catch (e) {}
        },

        pressKey(key) {
            this.phoneNumber += key;
            this.playDtmfTone();
            this.onPhoneInput();
        },

        backspace() {
            this.phoneNumber = this.phoneNumber.slice(0, -1);
            this.onPhoneInput();
        },

        async onPhoneInput() {
            const digits = this.phoneNumber.replace(/[^0-9]/g, '');
            if (digits.length >= 4) {
                try {
                    const res = await fetch(`/dialer/lookup?phone=${encodeURIComponent(digits)}`);
                    const data = await res.json();
                    this.contactMatch = data.match || null;
                } catch (e) {
                    this.contactMatch = null;
                }
            } else {
                this.contactMatch = null;
            }
        },

        toggleDialer() {
            this.isOpen = !this.isOpen;
        },

        handleOpenDialer(detail) {
            this.isOpen = true;
            if (detail && detail.phone) {
                this.phoneNumber = detail.phone;
                this.contactMatch = {
                    type: detail.type || 'customer',
                    id: detail.id || null,
                    name: detail.name || detail.phone,
                    city: detail.city || '',
                    badge: detail.badge || 'Direct Contact'
                };
            }
        },

        async initiateCall() {
            if (this.phoneNumber.trim().length < 5) return;
            this.isCalling = true;
            this.callStateText = 'Connecting Outbound Leg...';
            this.callTimer = 0;
            this.showWrapUp = false;

            try {
                await fetch('/dialer/start-call', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        phone: this.phoneNumber,
                        customer_id: this.contactMatch?.type === 'customer' ? this.contactMatch.id : null,
                        lead_id: this.contactMatch?.type === 'lead' ? this.contactMatch.id : null
                    })
                });
            } catch (e) {}

            setTimeout(() => {
                if (this.isCalling) {
                    this.callStateText = 'Call Active';
                    this.timerInterval = setInterval(() => {
                        this.callTimer++;
                    }, 1000);
                }
            }, 1200);
        },

        toggleMute() {
            this.isMuted = !this.isMuted;
        },

        hangUpCall() {
            clearInterval(this.timerInterval);
            this.isCalling = false;
            this.showWrapUp = true;
            this.callOutcome = this.callTimer > 5 ? 'Connected' : 'No Answer';
        },

        cancelWrapUp() {
            this.showWrapUp = false;
            this.callTimer = 0;
            this.phoneNumber = '';
            this.contactMatch = null;
        },

        async saveCallRecord() {
            this.isSavingCall = true;
            try {
                const res = await fetch('/dialer/end-call', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        phone: this.phoneNumber,
                        duration_seconds: this.callTimer,
                        outcome: this.callOutcome,
                        notes: this.callNotes,
                        customer_id: this.contactMatch?.type === 'customer' ? this.contactMatch.id : null,
                        lead_id: this.contactMatch?.type === 'lead' ? this.contactMatch.id : null
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.showWrapUp = false;
                    this.isOpen = false;
                    this.phoneNumber = '';
                    this.contactMatch = null;
                    this.callNotes = '';
                    this.callTimer = 0;
                }
            } catch (e) {
                alert('Failed to save call record.');
            } finally {
                this.isSavingCall = false;
            }
        },

        formatTimer(seconds) {
            const m = Math.floor(seconds / 60).toString().padStart(2, '0');
            const s = (seconds % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        }
    }
}
</script>
