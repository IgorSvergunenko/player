var saver = {

    init: function() {
        $('#savePhotos').click(this.getPhotos);
    },

    /**
     * get list of photos sorted by 10 pics
     */
    getPhotos: function() {

        $('#savePhotos').html('Идет сохранение...');
        $('#savePhotos').removeClass('btn-default');
        $('#savePhotos').addClass('btn-primary');

        var albumUrl = $('#albumUrl').val();
        $.ajax({
            'url': 'getPhotos',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'albumUrl': albumUrl
            },
            success: function(result) {
                if (!result.status) {
                    saver.resultRedirect('/result', result.status, '', '', '', result.message, result.statId);
                } else {
                    saver.savePhotos(result.photos, result.albumName, 0, result.photosCount, result.statId);
                }
            }
        })
    },

    /**
     * Save photos by parts
     *
     * @param photos
     * @param albumName
     * @param partNumber
     * @param photosCount
     * @param statId
     */
    savePhotos: function(photos, albumName, partNumber, photosCount, statId) {
        var photosPart = photos[partNumber]

        $.ajax({
            'url': 'savePhotos',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'photos': photosPart,
                'albumName': albumName
            },
            success: function(result) {
                partNumber = partNumber + 1;
                if (photos.length > partNumber) {
                    saver.savePhotos(photos, albumName, partNumber, photosCount, statId);
                }
                if (photos.length == partNumber) {
                    var status = result.status;
                    var link = result.link;
                    saver.resultRedirect('/result', status, link, photosCount, albumName, result.message, statId);
                }
            }
        })
    },

    resultRedirect: function(redirectUrl, status, link, savedCount, albumName, message, statId) {
        var form = $(
            '<form action="' + redirectUrl + '" method="post">' +
                '<input type="hidden" name="status" value="' + status +'" />' +
                '<input type="hidden" name="message" value="' + message +'" />' +
                '<input type="hidden" name="link" value="' + link +'" />' +
                '<input type="hidden" name="savedCount" value="' + savedCount +'" />' +
                '<input type="hidden" name="albumName" value="' + albumName +'" />' +
                '<input type="hidden" name="statId" value="' + statId +'" />' +
            '</form>'
        );
        $('body').append(form);
        $(form).submit();
    }

}

$(document).ajaxStart(function() {
    $('#loader').show();
}).ajaxStop(function(){
    $('#loader').hide();
});

$(function(){
    saver.init();
})