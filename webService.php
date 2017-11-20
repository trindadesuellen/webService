<?php
require 'Slim/Slim.php';
require_once "conexao.php";

\Slim\Slim::registerAutoloader();


$app = new \Slim\Slim();

$app->post('/cadastrar/', 'cadastrarUsuario'); //cadastrar usuario
$app->post('/autenticar/', 'autenticarUsuario'); //autenticar usuario
$app->get('/buscar/formulario/:id', 'buscarFormulario'); //buscar formulario cadastrado por usuario
$app->get('/buscar/campos/', 'buscarCampos'); //todos os campos disponiveis, caso o user escolha criar um novo formulario
$app->post('/cadastrar/formulario/', 'cadastrarFormulario'); //cadastrar novo formulario
$app->get('/buscar/formulario/salvo/:id', 'buscarFormularioSalvo');// busca formulario selecionado por id
$app->post('/cadastrar/clientes/', 'cadastrarCliente');//cadastrar  aum cliente no banco de dados
$app->get('/buscar/coordenador/', 'buscarCoordenador');//buscar usuários que sejam coordenadores, admin = 1
$app->post('/cadastrar/campo/', 'cadastrarCampo');//cadastrar novos campos para serem usados nos formulários
$app->post('/cadastrar/preferencia/', 'cadastrarPreferencia');//cadastrar preferencias de sincronização do usuario
$app->get('/buscar/usuario/:id', 'buscarUsuario');//buscar usuario coordenados pelo coordenador do id enviado
$app->get('/cadastrar/adm/', 'cadastrarAdm');//tornar usuário padrão um adm

$app->run();

	function cadastrarUsuario(){
		$email = $_POST['email'];
		$senha = $_POST['senha'];
		$nome = $_POST['nome'];
		$id_coordenador = $_POST['id_coordenador'];
		$sql = "INSERT INTO usuario(nome, senha, email, id_coordenador) VALUES ('$nome', '$senha', '$email', '$id_coordenador')";
		$query = mysql_query($sql);

		if($query){
			$array['id'] = "1";
			echo json_encode($array);
		}else{
			$array['id'] = "0";
			echo json_encode($array);
		}

	}

	function cadastrarAdm($id){
		$sql = "UPDATE usuario SET id_coordenador = '0', admin = '1' WHERE id = $id";
		$query = mysql_query($sql);

		if($query){
			$array['flag'] = "1";
			echo json_encode($array);
		}else{
			$array['flag'] = "0";
			echo json_encode($array);
		}
	}

	function cadastrarPreferencia(){
		$tipo = $_POST['tipo'];
		$id_usuario = $_POST['id_usuario'];

		$sql = "INSERT INTO sincronizacao(id_usuario, tipo) VALUES ($id_usuario, $tipo)";
		$query = mysql_query($sql);

		if($query){
			$array['id'] = "1";
			echo json_encode($array);
		}else{
			$array['id'] = "0";
			echo json_encode($array);
		}

	}

	function cadastrarCliente(){
		$id_usuario = $_POST['id_usuario'];
		$id_formulario = $_POST['id_formulario'];
		$campos = $_POST['campos'];
		$resposta = $_POST['respostas'];
	    $dt = date("Y-m-d H:i:s");

		$c = split("-", $campos);
		$r = split("-", $respostas);

		$result = count($c);
		$result2 = count($r);



		if($result == $result2)
		{
			$sql = "INSERT INTO cliente(id_user, id_form, data_cadastro) VALUES ($id_usuario, $id_formulario, '$dt')";
			$query = mysql_query($sql);
			if($query){
				$sql2 = "SELECT max(id) FROM cliente";
				$query2 = mysql_query($sql2);
				$row2 = mysql_fetch_array($query2);
				$id_cliente = $row2[0];
			

				for ($i=0; $i < $result; $i++) { 
					echo $sql3 = "INSERT INTO respostas(id_cliente, id_usuario, id_campo, resposta) VALUES ($id_cliente, $id_usuario, $c[$i], '$r[$i]')";
					$query3 = mysql_query($sql3);
				
				}

				if($query3){
					$array['flag']="1";
					echo json_encode($array);
				}else{
					$array['flag']="0";
					echo json_encode($array);

				}

			}

		}
	}




	function buscarFormulario($id){

		$sql = "SELECT admin, id_coordenador FROM usuario WHERE id = '$id'";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);

		if($row['admin'] == 0){
			
			$id_coordenador = $row['id_coordenador'];
			$sql2 = "SELECT nome, id FROM formulario WHERE id_usuario =  '$id_coordenador'";
			$query2 = mysql_query($sql2);
			$qtd2 = mysql_num_rows($query2);

		}else{

			$sql2 = "SELECT nome, id FROM formulario WHERE id_usuario = '$id'";
			$query2 = mysql_query($sql2);
			$qtd2 = mysql_num_rows($query2);
		}
		if($qtd2)
		{
		while($row2 = mysql_fetch_object($query2)){
			$array[] = $row2;
		}
		echo json_encode($array, JSON_FORCE_OBJECT);
		}
	}

	function autenticarUsuario(){
		$email= $_POST['email'];
		$senha= $_POST['senha'];
	    $sql = "SELECT id, admin FROM usuario WHERE email = '$email' AND senha = '$senha'";
		$query = mysql_query($sql);
		$qtd = mysql_num_rows($query);
		if($qtd)
		{
		$row = mysql_fetch_object($query);
		echo json_encode($row);
		}else{
			$array['id'] = "0";
			echo json_encode($array);
		}

	}

	function buscarCampos(){
		$sql = "SELECT nome, cod FROM campos";
		$query = mysql_query($sql);
		$qtd = mysql_num_rows($query);
		if($qtd)
		{
		while($row = mysql_fetch_object($query)){
			$array[] = $row;
		}
		echo json_encode($array, JSON_FORCE_OBJECT);
		}

	}

	function buscarUsuario($id){
		$sql = "SELECT id, nome FROM usuario WHERE id_coordenador = $id";
		$query = mysql_query($sql);
		$qtd = mysql_num_rows($query);
		if($qtd)
		{
		while($row = mysql_fetch_object($query)){
			$array[] = $row;
		}
		echo json_encode($array, JSON_FORCE_OBJECT);
		}
	}



	function buscarFormularioSalvo($id){
	$sql = "SELECT id_campos FROM formulario WHERE id = $id";
		$query = mysql_query($sql);

		
			$row = mysql_fetch_assoc($query);
			$rows = split("-", $row['id_campos']);
			$result = count($rows);
		

			for ($i=0; $i < $result-1; $i++) { 
				$sql2 = "SELECT nome, cod FROM campos WHERE cod = $rows[$i]";
				$query2 = mysql_query($sql2);
				$row2 = mysql_fetch_object($query2);
				$array[] = $row2;

			}


			echo json_encode($array, JSON_FORCE_OBJECT);
	}

	function cadastrarFormulario(){
		$campos= $_POST['campos'];
		$nome= $_POST['nome'];
		$id_usuario = $_POST['id_usuario'];
	    $sql = "INSERT INTO formulario(nome, id_usuario, id_campos) VALUES ('$nome', $id_usuario, '$campos')";
		$query = mysql_query($sql);

		if($query){
			$array['flag'] = "1";
			echo json_encode($array);
		}else{
			$array['flag'] = "0";
			echo json_encode($array);
		}
		
	}

	function buscarCoordenador(){

		$sql = "SELECT id, nome FROM usuario WHERE admin = 1";
		$query = mysql_query($sql);
		$qtd = mysql_num_rows($query);
		if($qtd)
		{
		while($row = mysql_fetch_object($query)){
			$array[] = $row;
		}
		echo json_encode($array, JSON_FORCE_OBJECT);
		}

	}

	function cadastrarCampo(){

		$nome= $_POST['nome'];
		//$tipo = $_POST['tipo'];
		$tipo = '2';
	    $sql = "INSERT INTO campos(nome, tipo) VALUES ('$nome', '$tipo')";
		$query = mysql_query($sql);

		if($query){
			$array['flag'] = "1";
			echo json_encode($array);
		}else{
			$array['flag'] = "0";
			echo json_encode($array);
		}
		
	}
		

