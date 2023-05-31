<?php
require_once 'conexao.php';

// Classe Prateleiras
class Prateleiras {
    private $conn;
    private $table_name = 'Prateleiras';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByNumero($numero) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Numero_Prateleira = :numero";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numero", $numero);
        $stmt->execute();
        return $stmt;
    }

    public function create($numero, $nome, $capacidade) {
        $query = "INSERT INTO " . $this->table_name . " (Numero_Prateleira, Nome, Capacidade_de_armazenamento) 
                VALUES (:numero, :nome, :capacidade)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numero", $numero);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":capacidade", $capacidade);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update($numero, $nome, $capacidade) {
        $query = "UPDATE " . $this->table_name . " SET Nome = :nome, Capacidade_de_armazenamento = :capacidade 
                WHERE Numero_Prateleira = :numero";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numero", $numero);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":capacidade", $capacidade);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($numero) {
        $query = "DELETE FROM " . $this->table_name . " WHERE Numero_Prateleira = :numero";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numero", $numero);
    
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

// Função para lidar com as requisições
function handleRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['numero'])) {
            // Requisição para obter uma prateleira por número
            $numero = $_GET['numero'];

            $prateleira = new Prateleiras($conn);
            $stmt = $prateleira->readByNumero($numero);
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Prateleira encontrada
                $prateleira_item = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($prateleira_item);
            } else {
                // Nenhuma prateleira encontrada
                echo json_encode(array('message' => 'Nenhuma prateleira encontrada.'));
            }
        } else {
            // Requisição para obter todas as prateleiras
            $prateleira = new Prateleiras($conn);
            $stmt = $prateleira->readAll();
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Prateleiras encontradas
                $prateleiras_arr = array();
                $prateleiras_arr['prateleiras'] = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $prateleira_item = array($row);
                    array_push($prateleiras_arr['prateleiras'], $prateleira_item);
                }

                echo json_encode($prateleiras_arr);
            } else {
                // Nenhuma prateleira encontrada
                echo json_encode(array('message' => 'Nenhuma prateleira encontrada.'));
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar uma nova prateleira
        $data = json_decode(file_get_contents("php://input"));

        $numero = $data->numero;
        $nome = $data->nome;
        $capacidade = $data->capacidade;

        $prateleira = new Prateleiras($conn);
        if ($prateleira->create($numero, $nome, $capacidade)) {
            echo json_encode(array('message' => 'Prateleira criada com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar a prateleira.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Requisição para atualizar uma prateleira existente
        $data = json_decode(file_get_contents("php://input"));

        $numero = $data->numero;
        $nome = $data->nome;
        $capacidade = $data->capacidade;

        $prateleira = new Prateleiras($conn);
        if ($prateleira->update($numero, $nome, $capacidade)) {
            echo json_encode(array('message' => 'Prateleira atualizada com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível atualizar a prateleira.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir uma prateleira
        $data = json_decode(file_get_contents("php://input"));
    
        $numero = $data->numero;
    
        $prateleira = new Prateleiras($conn);
        if ($prateleira->delete($numero)) {
            echo json_encode(array('message' => 'Prateleira excluída com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir a prateleira.'));
        }
    }
}

// Chamar a função handleRequest passando a conexão
handleRequest($conn);
?>
