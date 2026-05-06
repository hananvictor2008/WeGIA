<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if ($id_pessoa < 1) {
  http_response_code(400);
  echo json_encode(['erro' => 'O id da pessoa informado é inválido']);
  exit();
}

permissao($id_pessoa, 81, 7);

$id_projeto = filter_input(INPUT_GET, 'id_projeto', FILTER_SANITIZE_NUMBER_INT);

if (!$id_projeto || $id_projeto < 1) {
  header('Location: informacao_projeto.php?msg=ID do projeto inválido!');
  exit();
}

require_once ROOT . "/dao/ProjetoDAO.php";
require_once ROOT . "/controle/ProjetoControle.php";

$projetoControle = new ProjetoControle();

// Se vier do redirect do listarUm, usa a sessão; senão busca direto
if (isset($_SESSION['projeto'])) {
  $projeto = $_SESSION['projeto'];
  unset($_SESSION['projeto']);
} else {
  $projetoDAO = new ProjetoDAO();
  $projeto    = $projetoDAO->listarUm($id_projeto);
  if (!$projeto) {
    header('Location: informacao_projeto.php?msg=Projeto não encontrado!');
    exit();
  }
}

$tipos         = $projetoControle->obterTipos();
$locais        = $projetoControle->obterLocais();
$statusProjeto = $projetoControle->obterStatus();

require_once ROOT . "/html/personalizacao_display.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
?>
<!DOCTYPE html>
<html class="fixed">

<head>
  <meta charset="UTF-8">
  <title>Editar Projeto</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.css" />
  <link rel="stylesheet" href="../../assets/vendor/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
  <link rel="stylesheet" href="../../assets/vendor/magnific-popup/magnific-popup.css" />
  <link rel="icon" href="<?php display_campo("Logo", 'file'); ?>" type="image/x-icon">
  <link rel="stylesheet" href="../../assets/stylesheets/theme.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/skins/default.css" />
  <link rel="stylesheet" href="../../assets/stylesheets/theme-custom.css">

  <style>.obrig { color: rgb(255,0,0); }</style>
</head>

<body>
  <div id="header"></div>
  <div class="inner-wrapper">
    <aside id="sidebar-left" class="sidebar-left menuu"></aside>

    <section role="main" class="content-body">
      <header class="page-header">
        <h2>Editar Projeto</h2>
        <div class="right-wrapper pull-right">
          <ol class="breadcrumbs">
            <li><a href="../home.php"><i class="fa fa-home"></i></a></li>
            <li><span>Projetos</span></li>
            <li><span>Editar Projeto</span></li>
          </ol>
          <a class="sidebar-right-toggle"><i class="fa fa-chevron-left"></i></a>
        </div>
      </header>

      <div class="row">
        <div class="col-md-8 col-lg-8">
          <div class="tabs">
            <ul class="nav nav-tabs tabs-primary">
              <li class="active"><a href="#overview" data-toggle="tab">Editar Projeto</a></li>
            </ul>
            <div class="tab-content">
              <div id="overview" class="tab-pane active">
                <h4 class="mb-xlg">Informações do Projeto</h4>
                <h5 class="obrig">Campos Obrigatórios(*)</h5>

                <div class="form-group">
                  <label class="col-md-3 control-label" for="nome_projeto">Nome do Projeto<sup class="obrig">*</sup></label>
                  <div class="col-md-8">
                    <input type="text" class="form-control" id="nome_projeto" value="<?= htmlspecialchars($projeto['nome']) ?>" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 control-label" for="tipo_projeto">Tipo de Projeto<sup class="obrig">*</sup></label>
                  <div class="col-md-8">
                    <select class="form-control input-lg mb-md" id="tipo_projeto" required>
                      <option selected disabled>Selecionar</option>
                      <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= $tipo['id_tipo'] ?>" <?= ($tipo['id_tipo'] == $projeto['id_tipo']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($tipo['descricao']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 control-label" for="local_projeto">Local<sup class="obrig">*</sup></label>
                  <div class="col-md-8">
                    <select class="form-control input-lg mb-md" id="local_projeto" required>
                      <option selected disabled>Selecionar</option>
                      <?php foreach ($locais as $local): ?>
                        <option value="<?= $local['id_local'] ?>" <?= ($local['id_local'] == $projeto['id_local']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($local['nome']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 control-label" for="status_projeto">Status<sup class="obrig">*</sup></label>
                  <div class="col-md-8">
                    <select class="form-control input-lg mb-md" id="status_projeto" required>
                      <option selected disabled>Selecionar</option>
                      <?php foreach ($statusProjeto as $status): ?>
                        <option value="<?= $status['id_status'] ?>" <?= ($status['id_status'] == $projeto['id_status']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($status['descricao']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 control-label" for="data_inicio">Data de Início<sup class="obrig">*</sup></label>
                  <div class="col-md-8">
                    <input type="date" class="form-control" id="data_inicio" value="<?= htmlspecialchars($projeto['data_inicio']) ?>" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 control-label" for="data_fim">Data de Término</label>
                  <div class="col-md-8">
                    <input type="date" class="form-control" id="data_fim" value="<?= htmlspecialchars($projeto['data_fim'] ?? '') ?>">
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-md-3 control-label" for="descricao_projeto">Descrição</label>
                  <div class="col-md-8">
                    <textarea class="form-control" id="descricao_projeto" rows="4"><?= htmlspecialchars($projeto['descricao'] ?? '') ?></textarea>
                  </div>
                </div>

                <hr class="dotted short">

                <div class="panel-footer">
                  <div class="row">
                    <div class="col-md-9 col-md-offset-3">
                      <input type="hidden" id="csrf_token" value="<?= Csrf::generateToken() ?>">
                      <input type="hidden" id="id_projeto" value="<?= $id_projeto ?>">
                      <button type="button" class="btn btn-primary" id="btn-salvar">Salvar Alterações</button>
                      <a href="informacao_projeto.php" class="btn btn-default">Cancelar</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <script src="../../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../../assets/vendor/bootstrap/js/bootstrap.js"></script>
  <script src="../../Functions/projetos_editar.js"></script>
  <script>
    $(function() {
      $("#header").load("../header.php");
      $(".menuu").load("../menu.php");
    });
  </script>
</body>
</html>