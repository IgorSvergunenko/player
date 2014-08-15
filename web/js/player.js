var player = {

    init: function() {

        $('#searchTrack').click(player.searchTrack);
        $('#cleanPlayList').click(player.cleanPlayList);
        $('#trackName').keypress(function(e) {
            if (e.which==13) {
                player.searchTrack();
            }
        });

        $('#audio-player').mediaelementplayer({
            alwaysShowControls: true,
            features: ['playpause','volume','progress'],
            audioVolume: 'horizontal',
            audioWidth: 400,
            audioHeight: 120,
            success: function (mediaElement, domObject) {
                playerObj = mediaElement;
                mediaElement.addEventListener('ended', function (e) {
                    var currentSongNumber = $('#currentSongNumber').val()
                    player.getNextSong(currentSongNumber);
                    player.play(mediaElement);
                }, false);
            }
        });
    },

    cleanPlayList: function(mediaElement) {
        $('ul#playList').empty();
        $('#song-name').html('No song');
        $('#audio-player').attr('src', 'empty');
        $('#currentSongNumber').val('0');
    },

    play: function(mediaElement) {
        mediaElement.play();
        var currentSongNumber = $('#currentSongNumber').val();
        $('.playAction').removeClass('glyphicon-pause');
        $('.playAction').addClass('glyphicon-play');
        $('ul#playList li:eq(' +currentSongNumber+ ')'+' .playAction').addClass('glyphicon-pause');
    },

    // search songs and show on page
    searchTrack: function() {

        var searchValue = $('#trackName').val();

        $.ajax({
            url: "search",
            data: {
                'searchValue': searchValue
            },
            type: "POST",
            dataType: "json",
            success: function(response) {
                displaySongs(response);
            }
        });
    },

    addToPlayList: function(songArtist, songName, url) {

        var songName = songArtist + " - " + songName;

        // Play song from playlist
        $('.glyphicon-play').click(function() {
            var selectedSongEl = $(this).parents('li');
            var number = $(selectedSongEl).index();

            var songName = $(selectedSongEl).find('.table_song').text();
            var currentSong = $(selectedSongEl).find('.songUrl').val();
            $('#song-name').html(songName);
            $('#audio-player').attr('src', currentSong);
            $('#currentSongNumber').val(number);
            player.play(playerObj);
        });

        $('#playList').append(
            "<li>" +
                "<span class='table_song'>" + songName + "</span> " +
                "<input type='hidden' class='songUrl' value='"+url+"'>" +
                "<span class='glyphicon audio glyphicon-remove'></span>" +
                "<span class='playAction glyphicon audio glyphicon-play'></span>" +
                "</li>"
        );

        // Remove song from playlist
        $('.glyphicon-remove').click(function() {
            $(this).parents('li').remove()
        });
    },

    getNextSong: function(nextSongNumber) {
        nextSongNumber++;

        if (nextSongNumber >= $('ul#playList li').length) {
            //back to the top of playlist
            nextSongNumber = 0;
        }

        // get next song from playlist and add to player
        var selectedSongEl = 'ul#playList li:eq(' +nextSongNumber+ ')';
        var songName = $(selectedSongEl + ' .table_song').html();
        var src = $(selectedSongEl + ' .songUrl').val();
        $('#song-name').html(songName);
        $('#audio-player').attr('src', src);
        $('#currentSongNumber').val(nextSongNumber);
    },

    playSong: function() {
        var url = $(this).data('url');
        var songArtist = $(this).parents('tr').find('.table_title').text();
        var songName = $(this).parents('tr').find('.table_song').text();
        var currentSong = $('#audio-player').attr('src');

        if (currentSong == 'empty') {
            // No songs in playlist, add first
            $('#song-name').html(songArtist + " - " + songName);
            $('#audio-player').attr('src', url);
            player.addToPlayList(songArtist, songName, url);
            player.play(playerObj)
            return;
        }

        // Add song in to playlist
        player.addToPlayList(songArtist, songName, url);
    }

}

$(function(){
    player.init();
});