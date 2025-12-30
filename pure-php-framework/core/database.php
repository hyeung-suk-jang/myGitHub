<?php
/**
 * 순수 PHP 데이터베이스 클래스
 * PDO를 사용하지만 매우 단순한 래퍼
 */

class Database {
    private static $instance = null;
    private $connection = null;

    /**
     * 싱글톤 인스턴스 가져오기
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 생성자 - DB 연결
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed");
            }
        }
    }

    /**
     * 연결 가져오기
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * SELECT 쿼리 실행
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handle_error($e);
            return false;
        }
    }

    /**
     * 단일 행 가져오기
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->handle_error($e);
            return false;
        }
    }

    /**
     * INSERT/UPDATE/DELETE 실행
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->handle_error($e);
            return false;
        }
    }

    /**
     * INSERT 후 ID 반환
     */
    public function insert($sql, $params = []) {
        if ($this->execute($sql, $params)) {
            return $this->connection->lastInsertId();
        }
        return false;
    }

    /**
     * 테이블에서 전체 조회
     */
    public function selectAll($table, $where = '', $params = []) {
        $sql = "SELECT * FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        return $this->query($sql, $params);
    }

    /**
     * 테이블에서 단일 행 조회
     */
    public function selectOne($table, $where, $params = []) {
        $sql = "SELECT * FROM $table WHERE $where LIMIT 1";
        return $this->queryOne($sql, $params);
    }

    /**
     * ID로 조회
     */
    public function findById($table, $id) {
        return $this->selectOne($table, 'id = ?', [$id]);
    }

    /**
     * 삽입
     */
    public function insertData($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO $table (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        return $this->insert($sql, $values);
    }

    /**
     * 업데이트
     */
    public function updateData($table, $data, $where, $where_params = []) {
        $fields = [];
        $values = [];

        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }

        $sql = "UPDATE $table SET " . implode(', ', $fields) . " WHERE $where";

        // where 파라미터 추가
        $values = array_merge($values, $where_params);

        return $this->execute($sql, $values);
    }

    /**
     * 삭제
     */
    public function deleteData($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->execute($sql, $params);
    }

    /**
     * ID로 삭제
     */
    public function deleteById($table, $id) {
        return $this->deleteData($table, 'id = ?', [$id]);
    }

    /**
     * 카운트
     */
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $result = $this->queryOne($sql, $params);
        return $result ? $result['count'] : 0;
    }

    /**
     * 트랜잭션 시작
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * 커밋
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * 롤백
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * 에러 처리
     */
    private function handle_error($e) {
        if (DEBUG_MODE) {
            echo "Database Error: " . $e->getMessage();
        }
        error_log("Database Error: " . $e->getMessage());
    }
}

/**
 * DB 헬퍼 함수
 */
function db() {
    return Database::getInstance();
}
