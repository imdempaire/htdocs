<?php
    class homeModel{
        private $PDO;
        public function __construct()
        {
            require_once("db.php");
            // include '../_conexionMySQL.php';

            $pdo = new db();
            $this->PDO = $pdo->conexion();
        }
        public function agregarNuevoUsuario($nombre,$correo,$password,$colegio,$tipo,$pais,$provincia,$localidad){
            $statement = $this->PDO->prepare("INSERT INTO usuarios values(null,:nombre, :correo, :password, :colegio, :tipo, :pais, :provincia, :localidad)");
            $statement->bindParam(":nombre",$nombre);
            $statement->bindParam(":correo",$correo);
            $statement->bindParam(":password",$password);
            $statement->bindParam(":colegio",$colegio);
            $statement->bindParam(":tipo",$tipo);
            $statement->bindParam(":pais",$pais);
            $statement->bindParam(":provincia",$provincia);
            $statement->bindParam(":localidad",$localidad);
            
            try {
                $statement->execute();
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }
        public function obtenerclave($correo){
            $statement = $this->PDO->prepare("SELECT password FROM usuarios WHERE usuario = :correo");
            $statement->bindParam(":correo",$correo);
            return ($statement->execute()) ? $statement->fetch()['password'] : false;
        }
    }

?>