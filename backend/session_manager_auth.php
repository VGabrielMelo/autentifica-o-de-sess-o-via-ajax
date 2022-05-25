<?php 

  echo 'Iniciando o serviço de sessão...';
	session_start();	
	date_default_timezone_set('America/Sao_Paulo');
	
  $json = file_get_contents('../backend/dados/dados_para_load.json');
  $JsonData = json_decode($json,true);

  #Fazendo o carregamentos dos dados
	if ($_POST["operation"] == 'load') {
		
		if (isset($_SESSION["login"])) {
      echo json_encode($JsonData);

		} else {
      echo 'Ocorreu um erro!';
			echo '{ "nome" : "undefined" }';
		}
		

  # Realizando a tentativa de login pelos dados passados do HTML
	} else if ($_POST["operation"] == 'login') {
    echo 'Realizando Login...';
		if(!(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))){
        echo '{ "status" : "Não foi possível realizar o Login." }';
		   header('HTTP/1.0 401 Unauthorized');
		} else {
			$login = $_SERVER['PHP_AUTH_USER'];
			$senha = $_SERVER['PHP_AUTH_PW'];
			
			if ($login == $JsonData["login"] &&
				$senha == $JsonData["senha"]) {
				$_SESSION["nome"] = $JsonData["nome"];
				$_SESSION["login"] = $JsonData["login"];
				
        echo json_encode($JsonData);

			} else {
        echo 'Ocorreu um erro!';
        echo '{ "status" : "Login não realizado." }';
				 header('HTTP/1.0 401 Unauthorized');
			 }
		}

  #Logout da sessão
	} else if ($_POST["operation"] == 'logout') {
		echo 'Fazendo logout...';
		session_destroy();
		echo '{ "nome" : "undefined" }';
		

  #Deletar uma atividade
	}  else if ($_POST["operation"] == 'deletar') {
    echo 'Deletando atividade...';
		if (isset($_SESSION["login"])) {
      if (isset($_POST["index"]) && $_POST["index"] != ""){
        
        $index = $_POST["index"];
        $JsonData["atividades"] = array_filter($JsonData["atividades"], function($atividade) use ($index) {
          return $atividade["index"] != $index;
        });
        $JsonData['atividades'] = array_values($JsonData['atividades']);
        if(file_put_contents('../data/data.json', json_encode($JsonData))){
          echo json_encode($JsonData);
        } else {
          echo '{ "status" : "ERRO AO SALVAR!" }';
        }

      } else {
        echo '{ "status" : "ERRO!" }';
      }

    } else {
      echo '{ "status" : "Login não realizado." }';
      header('HTTP/1.0 401 Unauthorized');
    }

  } else if ($_POST["operation"] == 'Concluído') {
    echo 'Marcando atividade como concluída...';
		if (isset($_SESSION["login"])) {
      if (isset($_POST["index"]) && $_POST["index"] != "") {
        
        $index = $_POST["index"];

        $JsonData["atividades"] = array_map(function($atividade) use ($index) {
          if ($atividade["index"] == $index) {
            $atividade["status"] = "done";
          }
          return $atividade;
        }, $JsonData["atividades"]);
        
    
        if(file_put_contents('../data/data.json', json_encode($JsonData))){
          echo json_encode($JsonData);
        } else {
          echo '{ "status" : "ERRO AO SALVAR!" }';
        }

      }else{
        echo '{ "status" : "ERRO AO CONCLUIR!" }';
      }

    }else{
      echo '{ "status" : "Login não realizado." }';
      header('HTTP/1.0 401 Unauthorized');
    }
  
  }else if ($_POST["operation"] == 'Atualizar'){
    echo 'Salvando a edição da atividade...';
		if (isset($_SESSION["login"])) {
      if (isset($_POST["index"]) && $_POST["index"] != "" && isset($_POST["atividade"]) && $_POST["atividade"] != "" ){
        $index = $_POST["index"];
        $atividadeUpdate = $_POST["atividade"];
      
        $JsonData["atividades"] = array_map(function($atividade) use ($index, $atividadeUpdate) {
          if ($atividade["index"] == $index) {
            $atividade["atividade"] = $atividadeUpdate;
            $atividade["date"] = date("d/m/Y").' - '. date("H:i:s");
          }
          return $atividade;
        }, $JsonData["atividades"]);

        if(file_put_contents('../data/data.json', json_encode($JsonData))){
          echo json_encode($JsonData);
        } else {
          echo '{ "status" : "ERRO AO SALVAR!" }';
        }

      } else {
        echo '{ "status" : "ERRO AO LOGAR!" }';
      }

    }else{
      echo '{ "status" : "Login não realizado." }';
      header('HTTP/1.0 401 Unauthorized');
    }

  } else if ($_POST["operation"] == 'Inserir') {
    echo 'Adicionando atividade...';
		if (isset($_SESSION["login"])) {
      if (isset($_POST["atividade"]) && $_POST["atividade"] != ""){
        
        $atividade = $_POST["atividade"];

        $JsonData["atividades"][] = array(
          "index" => count($JsonData["atividades"]) + 1,
          "atividade" => $atividade,
          "date" => date("d/m/Y").' - '. date("H:i:s"),
          "status" => "open"
        );
          
        if(file_put_contents('../data/data.json', json_encode($JsonData))){
          echo json_encode($JsonData);
        } else {
          echo '{ "status" : "ERRO AO SALVAR!" }';
        }
      }else{
        echo '{ "status" : "ERRO AO INSERIR A ATIVIDADE" }';
      }

    }else{
      echo 'É necessário realizar o Login';
      echo '{ "status" : "Login não realizado." }';
      header('HTTP/1.0 401 Unauthorized');
    }

  } else {
		echo 'Ocorreu um erro!';
		echo '{ "invalid_operation" : "' . $_POST["operation"] . '" }';
		
	}
?>