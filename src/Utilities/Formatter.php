<?php
namespace Utilities;

class Formatter {

    public static function formatTrack($track) {
        return [
            'TrackId' => $track['TrackId'],
            'Name' => $track['Name'],
            'AlbumId' => $track['AlbumId'],
            'MediaType' => [
                'MediaTypeId' => $track['MediaTypeId'],
                'Name' => $track['MediaTypeName']
            ],
            'Genre' => [
                'GenreId' => $track['GenreId'],
                'Name' => $track['GenreName']
            ],
            'Composer' => $track['Composer'],
            'Milliseconds' => $track['Milliseconds'],
            'Bytes' => $track['Bytes'],
            'UnitPrice' => $track['UnitPrice']
        ];
    }

    public static function formatAlbum($album) {
        return [
            'AlbumId' => $album['AlbumId'],
            'Title' => $album['Title'],
            'Artist' => [
                'ArtistId' => $album['ArtistId'],
                'Name' => $album['ArtistName']
            ]
        ];
    }

}