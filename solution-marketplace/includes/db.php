<?php
/**
 * 데이터베이스 연결 및 쿼리 실행 클래스
 */

class Database {
    private $conn;
    private static $instance = null;

    private function __construct() {
        $this->connect();
    }

    /**
     * 싱글톤 인스턴스 반환
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 데이터베이스 연결
     */
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("데이터베이스 연결 실패: " . $e->getMessage());
        }
    }

    /**
     * PDO 연결 객체 반환
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * SELECT 쿼리 실행 (단일 행)
     */
    public function fetchOne($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * SELECT 쿼리 실행 (다중 행)
     */
    public function fetchAll($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * INSERT/UPDATE/DELETE 쿼리 실행
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * INSERT 후 생성된 ID 반환
     */
    public function insert($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 트랜잭션 시작
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * 트랜잭션 커밋
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * 트랜잭션 롤백
     */
    public function rollback() {
        return $this->conn->rollback();
    }

    /**
     * 영향받은 행 수 반환
     */
    public function rowCount($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }
}
