<?php
session_start();

if(isset($_SESSION['admin_logado'])){
    header("Location: admin.php");
    exit;
}
?>

<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/api/db_config.php';


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta name="google-adsense-account" content="ca-pub-4006894197637352">
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta name="copyright" content="© 2026 bUrY_+sUrF"/>
	<meta name="author" content="Juliano Desanti Carvalho Dalmora"/>
	<meta name="description" content="Previsão semanal de ondas & registro histórico fenomenológico da orla de Balneário Camboriú (blogue & vlogue)"/>
	<meta name="keywords" content="areia, surf, praia, onda, ondas, bc, balneário, camboriú, balneário camboriú"/>
	<meta property="og:title" content="bUrY_+sUrF" />
	<meta property="og:type" content="website"/>	
	<meta property="og:image" content="./css/img/bury_surf.jpg"/>
	<meta property="og:url" content="https://burysurf.com/blog.html"/>
	<meta property="og:description" content="Previsão semanal de ondas & registro histórico fenomenológico da orla de Balneário Camboriú (blogue & vlogue)"/>
	<meta property="og:locale" content="pt_BR" />
	<link rel="stylesheet" type="text/css" href="./css/style.css"/>
	<title>bUrY_+sUrF</title>
	<link rel="icon" type="image/x-icon" href="./css/img/favicon.png">
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-S5G2HVDDKB">
</script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag() { dataLayer.push(arguments); }
	gtag('js', new Date());
	gtag('config', 'G-S5G2HVDDKB');
</script>
<header>
        <div id="capa">
            <div id="logo">
                <img src="./css/img/burysurf.png" alt="logo">
            </div>
            <div id="lema">
                <span>
                    PIOR QUE A CULPA É A REINCIDÊNCIA
                </span>
            </div>
        </div>
</header>
<body>
    <main>
        <div id="divisor-tubo">
            <div id="corpo-tubo"></div>
            <div id="entrada-tubo"></div>
        </div>
        <div id="menub">
            <button id="menubar" onclick="linkhome()">INÍCIO</button>
            <button id="menubar" onclick="linkblog()">BLOGUE</button>
            <button id="menubar" onclick="linkvlog()">VLOGUE</button>
            <button id="menubar" onclick="linksobre()">SOBRE</button>
        </div>

        <div id="login">
            <h2 style="color:white;">Autentificação</h2>
            <div class="nav">
                <a href="index.php">← Voltar para a página principal</a>
            </div>           

            <form method="POST" action="api/login_process.php">
                <input type="text" name="usuario" placeholder="Usuário" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>
        </div>

<?php if(isset($_GET['erro'])): ?>
<p style="color:red;">Usuário ou senha inválidos.</p>
<?php endif; ?>

    </main>
    </body>
    <footer>
        <div id="copyright">
            <p>Copyright &copy; 2026 <span style="color:#00FF00;">bUrY_+sUrF</span>. Todos os direitos reservados.</p>
        </div>
    </footer>
    <script type="text/javascript" src="./js/BurySurfDB.js"></script>
    <script type="text/javascript" src="./js/main.js"></script>
</html>
