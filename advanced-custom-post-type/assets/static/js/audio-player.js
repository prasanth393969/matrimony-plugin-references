
/**
 * Custom ACPT audio player based on:
 * https://github.com/katspaugh/wavesurfer.js
 */
window.onload = function () {

    if(typeof WaveSurfer !== 'function'){
        return;
    }

    /**
     *
     * @param key
     * @return {string}
     */
    const localStorageKey = (key) => {
        return `acpt_playlist_${key}`;
    };

    /**
     *
     * @param time
     * @return {string}
     */
    const formatSeconds = (time) => {

        // Hours, minutes and seconds
        var hrs = ~~(time / 3600);
        var mins = ~~((time % 3600) / 60);
        var secs = ~~time % 60;

        // Output like "1:01" or "4:03:59" or "123:03:59"
        var ret = "";
        if (hrs > 0) {
            ret += "" + hrs + ":" + (mins < 10 ? "0" : "");
        }
        ret += "" + String(mins).padStart(2, '0') + ":" + (secs < 10 ? "0" : "");
        ret += "" + secs;

        return ret;
    };

    /**
     *
     * @param player
     * @param song
     */
    const dispatchChangeSongEvent = (player, song) => {
        const event = new CustomEvent(
            "acpt-change-song",
            {
                detail: {
                    song: song
                }
            }
        );

        player.dispatchEvent(event);
    };

    let isShuffle = localStorage.getItem("acpt_playlist_shuffle") === "1";
    let isAutoplay = localStorage.getItem("acpt_playlist_autoplay") === "1";

    /**
     * Playlists
     */
    const playlists = document.querySelectorAll(".acpt-custom-audio-playlist-wrapper");

    if(playlists.length > 0){
        playlists.forEach((playlist) => {

            const shuffle = playlist.querySelector(".shuffle");
            const autoplay = playlist.querySelector(".autoplay");
            const toggle = playlist.querySelector(".toggle");
            const list = playlist.querySelector(".acpt-audio-playlist");
            const songs = list.querySelectorAll("li");
            const player = playlist.querySelector(".acpt-custom-audio-player-wrapper");

            // add padding if needed
            const style = window.getComputedStyle(list);
            const maxHeight = style.getPropertyValue('max-height');
            const maxHeightValue = maxHeight.replace("px", "");
            const songsHeight = songs.length * 20;

            if(songsHeight > parseInt(maxHeightValue)){
                list.style.paddingRight = "5px";
            }

            if(isShuffle){
                shuffle.classList.add("active");
            }

            // shuffle
            shuffle.addEventListener("click", function (e) {
                e.preventDefault();

                if(shuffle.classList.contains("active")){
                    shuffle.classList.remove("active");
                    localStorage.setItem("acpt_playlist_shuffle", "0");
                    isShuffle = false;
                } else {
                    shuffle.classList.add("active");
                    localStorage.setItem("acpt_playlist_shuffle", "1");
                    isShuffle = true;
                }
            });

            if(isAutoplay){
                autoplay.classList.add("active");
            }

            // autoplay
            autoplay.addEventListener("click", function (e) {
                e.preventDefault();

                if(autoplay.classList.contains("active")){
                    autoplay.classList.remove("active");
                    localStorage.setItem("acpt_playlist_autoplay", "0");
                    isAutoplay = false;
                } else {
                    autoplay.classList.add("active");
                    localStorage.setItem("acpt_playlist_autoplay", "1");
                    isAutoplay = true;
                }
            });

            // toggle list
            toggle.addEventListener("click", function (e) {
                e.preventDefault();

                if(toggle.classList.contains("reverse")){
                    toggle.classList.remove("reverse");
                    list.classList.remove("hidden");
                } else {
                    toggle.classList.add("reverse");
                    list.classList.add("hidden");
                }
            });

            // click on list songs
            songs.forEach((song) => {
                const meta = song.querySelector(".meta");
                const id = song.id;
                const src = song.dataset.src;
                const title = song.dataset.title;
                const album = song.dataset.album;
                const artist = song.dataset.artist;
                const thumbnail = song.dataset.thumbnail;

                meta.addEventListener("click", function(e){
                    e.preventDefault();

                    dispatchChangeSongEvent(player, {
                        id: id,
                        src: src,
                        title: title,
                        album: album,
                        artist: artist,
                        thumbnail: thumbnail
                    });
                });
            });
        });
    }

    /**
     * Audio player
     */
    const singlePlayers = document.querySelectorAll(".acpt-custom-audio-player-wrapper");

    if(singlePlayers.length > 0){
        singlePlayers.forEach((player) => {

            const playlist = player.nextElementSibling ? player.nextElementSibling.nextElementSibling : null;
            const theme = player.dataset.theme;
            const loading = player.querySelector(".loading");
            const wave = player.querySelector(".wave");
            const controls = player.querySelector(".controls");
            const playButton = player.querySelector(".play");
            const ffButton = player.querySelector(".ff");
            const rwButton = player.querySelector(".rw");
            const prevButton = player.querySelector(".prev");
            const nextButton = player.querySelector(".next");
            const timer = player.querySelector(".timer");
            const volume = player.querySelector(".volume");
            const mute = player.querySelector(".mute");

            const waveColor = theme === 'dark' ? "#ffffff" : "#777777";
            const progressColor = theme === 'dark' ? "#007cba" : "#007cba";
            const cursorColor = theme === 'dark' ? "#aaaaaa" : "#ccd0d4";

            const songs = [];

            if(playlist && playlist.classList.contains("acpt-audio-playlist")){
                const items = playlist.querySelectorAll("li");

                items.forEach((s) => {
                    songs.push({
                        id: s.id,
                        src: s.dataset.src,
                        title: s.dataset.title,
                        artist: s.dataset.artist,
                        album: s.dataset.album,
                        thumbnail: s.dataset.thumbnail,
                    });
                });
            }

            const wavesurfer = WaveSurfer.create({
                height: 64,
                width: "100%",
                container: wave,
                waveColor: waveColor,
                progressColor: progressColor,
                cursorColor: cursorColor,
                cursorWidth: 2,
                url: wave.dataset.src,
                barRadius: 4,
                barWidth: 4,
            });

            // update the timer
            const timerUpdate = () => {
                const totalTime = wavesurfer.getDuration(),
                    currentTime = wavesurfer.getCurrentTime(),
                    remainingTime = totalTime - currentTime;

                timer.innerHTML = `${formatSeconds(currentTime)} / ${formatSeconds(totalTime)}`;
            };

            // play
            playButton.addEventListener("click", function(e){
                e.preventDefault();

                if(wavesurfer.isPlaying()){
                    playButton.classList.remove("pause");
                } else {
                    playButton.classList.add("pause");
                }

                wavesurfer.playPause();
            });

            // +15 sec
            ffButton.addEventListener("click", function(e){
                e.preventDefault();
                const goto = wavesurfer.getCurrentTime() + 15;
                wavesurfer.setTime(goto);
            });

            // -15 sec
            rwButton.addEventListener("click", function(e){
                e.preventDefault();
                const goto = wavesurfer.getCurrentTime() - 15;
                wavesurfer.setTime(goto);
            });

            // prev
            if(prevButton){
                prevButton.addEventListener("click", function(e){
                    e.preventDefault();
                    const index = songs.findIndex((a) => a.id === player.id);
                    let prevSong;

                    if(isShuffle){
                        prevSong = songs.filter((a) => a.id !== player.id)[Math.floor(Math.random()*songs.length)];
                    } else {
                        prevSong = songs[index-1];
                    }

                    if(prevSong){
                        dispatchChangeSongEvent(player, prevSong);
                    }
                });
            }

            // next
            if(nextButton){
                nextButton.addEventListener("click", function(e){
                    e.preventDefault();

                    const index = songs.findIndex((a) => a.id === player.id);
                    let nextSong;

                    if(isShuffle){
                        nextSong = songs.filter((a) => a.id !== player.id)[Math.floor(Math.random()*songs.length)];
                    } else {
                        nextSong = songs[index+1];
                    }

                    if(nextSong){
                        dispatchChangeSongEvent(player, nextSong, wavesurfer.isPlaying());
                    }
                });
            }

            // volume
            volume.addEventListener("change", function (e) {
                const vol = e.target.value;

                if(vol > 0){
                    wavesurfer.setMuted(false);
                    mute.classList.remove("muted");
                } else {
                    wavesurfer.setMuted(true);
                    mute.classList.add("muted");
                }

                wavesurfer.setVolume(vol);
            });

            // mute
            mute.addEventListener("click", function(e){
                e.preventDefault();

                if(!wavesurfer.getMuted()){
                    wavesurfer.setMuted(true);
                    mute.classList.add("muted");
                    volume.value = 0;
                } else {
                    wavesurfer.setMuted(false);
                    mute.classList.remove("muted");
                    volume.value = 1;
                    wavesurfer.setVolume(1);
                }
            });

            // events
            wavesurfer.on('ready', function () {
                controls.style.display = 'flex';
                timerUpdate();
                loading.remove();
            });

            wavesurfer.on('audioprocess', function () {
                timerUpdate()
            });

            wavesurfer.on('finish', function () {
                playButton.classList.remove("pause");

                if(songs.length > 0){
                    const index = songs.findIndex((a) => a.id === player.id);
                    let nextSong;

                    if(isShuffle){
                        nextSong = songs.filter((a) => a.id !== player.id)[Math.floor(Math.random()*songs.length)];
                    } else {
                        nextSong = songs[index+1];
                    }

                    if(nextSong){
                        dispatchChangeSongEvent(player, nextSong);
                    }
                }
            });

            // change playlist song event
            player.addEventListener("acpt-change-song", function (e) {

                const newSong = e.detail.song;
                const img = player.querySelector(".thumbnail");
                const meta = player.querySelector(".meta");
                const title = meta.querySelector("h4");
                const artist = meta.querySelector(".artist");
                const album = meta.querySelector(".album");

                player.id = newSong.id;
                title.innerText = newSong.title;
                artist.innerText = newSong.artist;
                album.innerText = newSong.album;
                wave.dataset.src = newSong.src;
                wavesurfer.load(newSong.src);

                if(img){
                    img.src = newSong.thumbnail;
                    img.alt = newSong.title;
                }

                const playlist = player.nextElementSibling.nextElementSibling;

                if(playlist && playlist.classList.contains("acpt-audio-playlist")) {
                    const items = playlist.querySelectorAll("li");

                    items.forEach((song) => {
                        if(song.id === newSong.id){
                            song.classList.add("active");
                        } else {
                            song.classList.remove("active");
                        }
                    });
                }

                wavesurfer.on('ready', function () {
                    if(isAutoplay === true){
                        wavesurfer.play();
                    } else {
                        playButton.classList.remove("pause");
                        wavesurfer.setTime(0);
                    }
                });
            });
        });
    }
};
