<?php
// todo :: подключить модули, сделать разделение
// чтобы не в одном было

class DB {

    public $conn;
    // подключаем модули
    public $user;
    public $snake;
    public $food;
    public $map;

    public function __construct() {
        $host = 'localhost';
        $dbName = 'snake_of_pi';
        $user = 'mysql';
        $pass = 'mysql';

        $this->conn = new PDO('mysql:dbname='.$dbName.';host='.$host, $user, $pass);
    }

    // TODO :: вынести пользователя в отдельный модуль

	// текущее время на сервере
	public function getServerTime() {
		$sql = 'SELECT CURRENT_TIMESTAMP() AS time';
        $stm = $this->conn->prepare($sql);
        $stm->execute();
        return $stm->fetchObject('stdClass');

	}
	
	
	
	
	
	
    /*User*/
    // Получение пользователя
    public function getUsers() {
        $query = 'SELECT id, name, login FROM user ORDER BY id DESC';
        return $this->conn->query($query)->fetchAll(PDO::FETCH_CLASS);
    }
    //Получить пользователя по логину
    public function getUserByLogin($login) {
        $sql = 'SELECT name, login, token FROM user WHERE login = :login ORDER BY id DESC LIMIT 1';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':login', $login, PDO::PARAM_STR);
        $stm->execute();
        return $stm->fetchObject('stdClass');
    }
    //Получить пользоваетеля по токену
    public function getUserByToken($token) {
        $sql = 'SELECT * FROM user WHERE token = :token';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':token', $token, PDO::PARAM_STR);
        $stm->execute();
        return $stm->fetchObject('stdClass');
    }
    //Получить пользователя по id
    public function getUserById($id) {
        $sql = 'SELECT id, login, name FROM user WHERE id = :id ORDER BY id DESC LIMIT 1';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchObject('stdClass');
    }
    //Получить пользователя
    public function getUser($login, $password) {
        $sql = 'SELECT * FROM user WHERE login = :login and password = :password';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':login', $login, PDO::PARAM_STR);
        $stm->bindValue(':password', $password, PDO::PARAM_STR);
        $stm->execute();
        return $stm->fetchObject('stdClass');
    }
    // Изменить токен пользователя
    public function updateUserToken($id, $token) {
        $sql = "UPDATE user SET token = :token WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function updateUserScore($id, $score) {
        $sql = "UPDATE user SET score = :score WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':score', $score, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }
    //Создать пользователя
    public function saveUser($options) {
        $name = $options['name'];
        $login = $options['login'];
        $password = $options['password'];

        $sql = "INSERT INTO user (name, login, password) VALUES (:name, :login, :password)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':login', $login, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $res = $stmt->execute();
        return $res;
    }
    


    
    


    /*Snake*/
    // Получение питона
    public function getSnakes($map_id) {
        $sql = 'SELECT * FROM snake WHERE map_id = :map_id ORDER BY id DESC';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':map_id', $map_id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_CLASS);
    }
    
    // Получить питона по id
    public function getSnakeById($id) {
        $sql = 'SELECT * FROM snake WHERE id = :id';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchObject('stdClass');
    }
    // Получить последнего созданного питона у пользователя
    public function getSnakeByUserId($id) {
        $sql = 'SELECT * FROM snake WHERE user_id = :id ORDER BY id DESC';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchObject('stdClass');
    }
    // Создать питона
    public function createSnake($options) {
        $user_id = $options->user_id;
        $map_id = $options->map_id;
        $direction = 'right';
        if(isset($options->direction)) {
            $direction = $options->direction;
        }
        $eating = 0;
        if(isset($options->eating)) {
            $eating = $options->eating;
        }

        $sql = "INSERT INTO snake (user_id, direction, map_id, eating) VALUES (:user_id, :direction, :map_id, :eating)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':map_id', $map_id, PDO::PARAM_INT);
        $stmt->bindParam(':eating', $eating, PDO::PARAM_INT);
        $stmt->bindParam(':direction', $direction, PDO::PARAM_STR);
        $res = $stmt->execute();
        return $res;
    }
    // обновить всех питонов
    public function updateSnakes($map_id, $snakes) {
        $res = false;
        foreach ($snakes as $snake) {
            if(isset($snake->id)) {
                if(isset($snake->deleted_at)) {
                    $res = $this->deleteSnake($snake->id);
                    $res = $this->deleteSnakeBodyFromSnake($snake->id);
                } else {
                    $res = $this->updateSnake($snake);
                    $res = $this->updateSnakesBody($snake);
                }
            } else {
                $snake->map_id = $map_id;
                $res = $this->createSnake($snake);
            }
        }
        return $res;
    }
    // обновить змейку
    public function updateSnake($snake) {
        $sql = "UPDATE snake SET direction = :direction, eating = :eating WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':eating', $snake->eating, PDO::PARAM_INT);
        $stmt->bindParam(':direction', $snake->direction, PDO::PARAM_STR);
        $stmt->bindParam(':id', $snake->id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    // Изменить направление питона
    public function updateSnakeDirection($id, $direction) {
        $sql = "UPDATE snake SET direction = :direction WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':direction', $direction, PDO::PARAM_STR);
        $res = $stmt->execute();
        return $res;
    }
    // Удалить питона
    public function deleteSnake($id) {
        $sql = "DELETE FROM snake WHERE id =  :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }
    // Удалить питона пользователя
    public function deleteUserSnakes($user_id) {
        $sql = "DELETE FROM snake WHERE user_id =  :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }




    /*Snake_body*/
    public function getSnakesBody() {
        $query = 'SELECT * FROM snake_body ORDER BY id DESC';
        return $this->conn->query($query)->fetchAll(PDO::FETCH_CLASS);
    }
    public function getSnakesBodyCountBySnake($id) {
        $sql = 'SELECT count(*) FROM snake_body WHERE snake_id = :id ORDER BY id DESC';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchColumn();
    }
    // Получить тело питона
    public function getSnakeBody($id)
    {
        $sql = 'SELECT * FROM snake_body WHERE snake_id = :id ORDER BY id DESC';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_CLASS);
    }
    
    // Создать тело питона
    public function createSnakeBody($options) {
        $snake_id = $options->snake_id;
        $x = $options->x;
        $y = $options->y;

        $sql = "INSERT INTO snake_body (snake_id, x, y) VALUES (:snake_id, :x, :y)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':snake_id', $snake_id, PDO::PARAM_INT);
        $stmt->bindParam(':x', $x, PDO::PARAM_INT);
        $stmt->bindParam(':y', $y, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }
    // обновить все тела змейки
    public function updateSnakesBody($snake) {
        $res = false;
        if(isset($snake->body)) {
            $body = $snake->body;
            foreach ($body as $item) {
                if (isset($item->id)) {
                    if(isset($item->deleted_at)) {
                        $res = $this->deleteSnakeBody($item->id);
                    } else {
                        $res = $this->updateSnakeBody($item);
                    }
                } else {
                    if(isset($snake->id)) {
                        $item->snake_id = $snake->id;
                        $res = $this->createSnakeBody($item);
                    }
                }

            }
        }
        return $res;
    }
    // обновить тело змейки
    public function updateSnakeBody($item) {
        if($item) {
            $sql = "UPDATE snake_body SET x = :x, y = :y WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':x', $item->x, PDO::PARAM_INT);
            $stmt->bindParam(':y', $item->y, PDO::PARAM_INT);
            $stmt->bindParam(':id', $item->id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        return false;
    }
    // Удалить тело питона
    public function deleteSnakeBody($id) {
        $sql = "DELETE FROM snake_body WHERE id =  :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }
    // Удалить часть тела питона
    public function deleteSnakeBodyFromSnake($id) {
        $sql = "DELETE FROM snake_body WHERE snake_id =  :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }




    /*Food*/
    // Создать еду
    public function createFood($options) {
        $ftype = $options['type'];
        $fvalue = $options['value'];
        $x = $options['x'];
        $y = $options['y'];
        $map_id = $options['map_id'];

        $sql = "INSERT INTO snake_body (ftype, fvalue, x, y, map_id) VALUES (:ftype, :fvalue, :x, :y, :map_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ftype', $ftype, PDO::PARAM_INT);
        $stmt->bindParam(':fvalue', $fvalue, PDO::PARAM_INT);
        $stmt->bindParam(':x', $x, PDO::PARAM_INT);
        $stmt->bindParam(':y', $y, PDO::PARAM_INT);
        $stmt->bindParam(':map_id', $map_id, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }
    public function getFoods($map_id) {
        $sql = 'SELECT * FROM food WHERE map_id = :map_id ORDER BY id DESC';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':map_id', $map_id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_CLASS);
    }
    // Удалить еду
    public function deleteFood($id) {
        $sql = "DELETE FROM food WHERE id =  :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }
    public function updateFoods($map_id, $foods) {
        $res = false;
        foreach ($foods as $food) {
            if(isset($snake->id)) {
                if(isset($snake->deleted_at)) {
                    $res = $this->deleteFood($food->id);
                } else {
                    $res = $this->updateFood($food);
                }
            } else {
                $food->map_id = $map_id;
                $res = $this->createFood($food);
            }
        }
        return $res;
    }
    public function updateFood($food) {
        $sql = "UPDATE food SET x = :x, y = :y WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':x', $food->x, PDO::PARAM_INT);
        $stmt->bindParam(':x', $food->y, PDO::PARAM_INT);
        $stmt->bindParam(':id', $food->id, PDO::PARAM_INT);
        return $stmt->execute();
    }



    /*Map*/
    // Получение карты
    public function getMaps() {
        $query = 'SELECT * FROM map';
        return $this->conn->query($query)->fetchAll(PDO::FETCH_CLASS);
    }
    // Получить карту по id
    public function getMapById($id) {
        $sql = 'SELECT * FROM map WHERE id = :id';
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchObject('stdClass');
    }
    // Создать карту
    public function createMap($options) {
        $width = $options['width'];
        $height = $options['height'];

        $sql = "INSERT INTO map (width, height) VALUES (:width, :height)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':width', $width, PDO::PARAM_INT);
        $stmt->bindValue(':height', $height, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }
    // Изменить последнего времени обновления
    public function updateMapLastUpdated($id) {
        $sql = "UPDATE map SET last_updated = CURRENT_TIMESTAMP() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    }



}
    