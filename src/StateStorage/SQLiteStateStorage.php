<?php
//
// * Created by PhpStorm.
// * User: Администратор
// * Date: 12.09.2016
// * Time: 17:11
//
//
//namespace SmkSoftware\ScrapeIt\StateStorage;
//
//
//use PDO;
//use SmkSoftware\ScrapeIt\Request;
//use SmkSoftware\ScrapeIt\Settings;
//use SQLite3;
//
//class SQLiteStateStorage extends StateStorageBase
//{
//    const FILE_NAME = 'file';
//
//    /** @var SQLite3 */
//    private $db;
//
//    private static $createSql = <<<EOF
//        CREATE TABLE urls (
//            url TEXT PRIMARY KEY NOT NULL
//        );
//
//        CREATE TABLE completed (
//            url TEXT PRIMARY KEY NOT NULL
//        );
//EOF;
//
//    private static $nextSql = 'SELECT url, method, params FROM urls LIMIT :limit';
//    private $nextStmt;
//
//
//
//    private static $addSql = 'INSERT INTO urls (url) WHERE url=:url';
//
//    private static $exitstSql = <<<EOF
//        SELECT
//            EXISTS(SELECT 1 FROM urls WHERE url=:url) OR
//            EXISTS(SELECT 1 FROM completed WHERE url=:url)
//EOF;
//
//    private static $completeInsertSql = 'INSERT INTO completed (url) VALUES (:url)';
//
//
//
//
//    public function complete(Request $request)
//    {
//        $this->db->();
//        $this->db->
//        // TODO: Implement complete() method.
//    }
//
//    public function next()
//    {
//        if (!$this->nextStmt) {
//            $this->nextStmt = $this->db->prepare(self::$nextSql);
//        }
//        $this->nextStmt->b
//    }
//
//    public function init(Settings $settings) {
//        parent::init($settings);
//
//        $this->db = new PDO('sqlite:'.$this->settings->getSetting(self::FILE_NAME));
//        if (!$this->db) {
//            echo $this->db->lastErrorMsg();
//            return false;
//        }
//        $ret = $this->db->exec(self::$createSql);
//        if (!$ret) {
//            echo $this->db->lastErrorMsg();
//            return false;
//        }
//        return true;
//    }
//
//    public function destroy()
//    {
//        if (!$this->db)
//            $this->db->close();
//    }
//
//    // TODO: implement addUrls!
//
//    public function add(Request $request)
//    {
//        if (!$this->addStmt)
//            $this->addStmt = $this->db->prepare(self::$addSql);
//
//        $this->addStmt->bindValue(':url',$request->url);
//
//        //$stmt->bindValue(':url', 1, SQLITE3_INTEGER);
//        //$this->insertStmt = $db->prepare($this->insertSql);
//    }
//}