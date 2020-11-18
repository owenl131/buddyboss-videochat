
jQuery( document ).ready(function() {
	const connect = window.Twilio.Video.connect;

	var token = document.getElementById("video-user-token").value;
	var roomname = document.getElementById("video-room").value;

	connect(token, { audio: true, video: { width: 640 } }).then(room => {
		
		console.log(`Successfully joined a Room: ${room}`);
		
		const localParticipant = room.localParticipant;
		
		localParticipant.tracks.forEach(publication => {
			document.getElementById('local-media-div').appendChild(publication.track.attach());
		});
		
		room.participants.forEach(participant => {
			console.log(`Participant "${participant.identity}" is connected to the Room`);
			participant.tracks.forEach(publication => {
				if (publication.track) {
					document.getElementById('remote-media-div').appendChild(publication.track.attach());
				}
			});
			participant.on('trackSubscribed', track => {
				document.getElementById('remote-media-div').appendChild(track.attach());
			});
		});
		
		room.on('participantConnected', participant => {
			console.log(`A remote Participant connected: ${participant}`);
			participant.tracks.forEach(publication => {
				if (publication.isSubscribed) {
					const track = publication.track;
					document.getElementById('remote-media-div').appendChild(track.attach());
				}
			});
			participant.on('trackSubscribed', track => {
				document.getElementById('remote-media-div').appendChild(track.attach());
			});
		});
		
		room.on('participantDisconnected', participant => {
			console.log(`Participant "${participant.identity}" has disconnected from the Room`);
		});
		
		room.on('disconnected', room => {
			room.localParticipant.tracks.forEach(publication => {
				const attachedElements = publication.track.detach();
				attachedElements.forEach(element => element.remove());
			});
		});
		
		window.addEventListener("beforeunload", e => room.disconnect());
		
	}, error => {
		console.error(`Unable to connect to Room: ${error.message}`);
	});
});
