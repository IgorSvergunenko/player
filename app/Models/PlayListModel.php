<?php
/**
 *
 * Date: 03.02.14
 * Time: 17:57
 */

namespace App\Models;

class PlayListModel
{
    /**
     * @var
     */
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * get all songs from play list
     *
     * @return array
     */
    public function getAllPlayLists()
    {
        $sql = "SELECT * FROM play_lists";

        $playLists = $this->db->fetchAll($sql);

        return $playLists;
    }

    public function getPlayListSongs($playListId)
    {
        $sql = "SELECT
              s.link,
              s.title
            FROM play_lists pl
              LEFT JOIN play_list_songs pls
                ON pls.playListId = pl.id
              LEFT JOIN songs s
                ON s.id = pls.songId
            WHERE pl.id = ?";

        $songs = $this->db->fetchAll($sql, [$playListId]);

        return $songs;
    }

    public function savePlayList($title, $songs)
    {
        if (empty($songs) || empty($title))
            return '';

        $this->db->insert('play_lists', ['name' => $title, 'date' => date('Y-m-d H:i:s'), 'userId' => 1]);
        $playListId = $this->db->lastInsertId();

        foreach($songs as $song) {
            $id = $this->getSongId($song);
            $this->db->insert('play_list_songs', ['songId' => $id, 'playListId' => $playListId]);
        }
    }

    public function saveSong($data) {
        $this->db->insert('songs', $data);
        return $this->db->lastInsertId();
    }

    public function getSongId($newSong) {

        $sql = "SELECT * FROM songs WHERE title = ?";
        $songData = $this->db->fetchAll($sql, array($newSong['title']));

        if (empty($songData)) {
            $id = $this->saveSong($newSong);
        } else {
            $id = $songData[0]['id'];
        }

        return $id;
    }
}