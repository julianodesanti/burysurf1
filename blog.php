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
<body>
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

		<div id="blog">
			<div id="titulo">
				<span>SUPERFÍCIE: A NOVA BANCADA DE AREIA</span>
			</div>
			<div id="redimencionamento">
				<!-- O conteúdo do blog será inserido aqui via AJAX -->
			</div>
		</div>
	</main>

	<footer>
		<div id="copyright">
			<p>
				Copyright &copy; 2026 <span style="color:#00FF00;">bUrY_+sUrF</span>. Todos os direitos reservados.
			</p>
		</div>
	</footer>	

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script type="text/javascript" src="./js/main.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			// Fazendo a requisição AJAX para get_posts.php
			$.ajax({
				url: 'get_posts.php', // Local da API
				type: 'GET',
				dataType: 'json', // Esperando resposta JSON
				success: function(data) {
					if (data.length > 0) {
						// Para cada post retornado pela API, adiciona _+ e os dados do post
						$.each(data, function(index, post) {
							var postHtml = `
								<div id="titulo">
									<span>_+</span>
								</div>
								<article>
									<h2>${post.titulo}</h2>
							<p class="data">Publicado em ${new Date(post.publication_date).toLocaleDateString()}</p>
							${post.imagem ? `<img src="/upload_posts/${post.imagem}" alt="${post.titulo}">` : ''}
									<p>${post.conteudo}</p>
								</article>
							`;
							// Inserindo o HTML gerado no container
							$('#redimencionamento').append(postHtml);
						});
					} else {
						$('#redimencionamento').html('<p>Nenhum post encontrado.</p>');
					}
				},
				error: function() {
					$('#redimencionamento').html('<p>Erro ao carregar os posts.</p>');
				}
			});
		});
	</script>

</body>
</html>