class VolumeProcessor extends AudioWorkletProcessor {
	constructor() {
		super();
		this.instant = 0.0;
		this.averaged = 0.0;
        this._updateIntervalInMS = 25;
        this.ended = false;
        this._nextUpdateFrame = this._updateIntervalInMS;
        this.port.onmessage = (event) => {
            if (event.data.msg) {
                if (event.data.msg === "end") {
                    this.ended = true;
                }
            }
        };
	}
    get intervalInFrames () {
        return this._updateIntervalInMS / 1000 * sampleRate;
    }
	process (inputs, outputs, parameters) {
        if (ended) {
            return false;
        }
        if (inputs.length == 0) {
            return true;
        }
		// take the first output
		const input = inputs[0];
		// fill each channel with random values multiplied by gain
		if (input.length == 0) {
			return true;
		}
		const samples = input[0];
		
		let sum = 0.0;
		for (let i = 0; i < samples.length; i++) {
			sum += samples[i] * samples[i];
		}
		this.instant = Math.sqrt(sum / samples.length);
		this.averaged = Math.max(this.instant, 0.95 * this.averaged);
        this._nextUpdateFrame -= samples.length;
        if (this._nextUpdateFrame < 0) {
            this._nextUpdateFrame += this.intervalInFrames;
            this.port.postMessage({volume: this.averaged });
        }
		return true;
	}
}
registerProcessor('volume-processor', VolumeProcessor);
