<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/api/check_auth.php';
require_once __DIR__ . '/api/db_config.php';

$spots = [];
$sql = "SELECT ss.spot_id as id, ss.spot_name as name, ss.image, sc.wave_size, sc.wave_formation, sc.weather, sc.wind, sc.water_temperature FROM surf_spots ss LEFT JOIN surf_conditions sc ON ss.spot_id = sc.spot_id AND sc.condition_date = CURDATE() ORDER BY ss.spot_id";
    $res = $conn->query($sql);
        if ($res) {while ($r = $res->fetch_assoc()) $spots[] = $r;}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="google-adsense-account" content="ca-pub-4006894197637352">
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="copyright" content="© 2026 bUrY_+sUrF"/>
    <meta name="author" content="Juliano Desanti Carvalho Dalmora"/>
    <meta name="description"
		content="Weekly wave forecast & historical phenomenological record of the Balneário Camboriú coastline (blog & vlog)." />
	<meta name="keywords" content="sand, surf, beach, wave, waves, bc, balneário, camboriú, balneário camboriú" />
	<meta property="og:title" content="bUrY_+sUrF" />
	<meta property="og:type" content="website" />
	<meta property="og:image" content="./css/img/bury_surf.jpg" />
	<meta property="og:url" content="https://burysurf.com/vlog.html" />
	<meta property="og:description"
		content="Weekly wave forecast & historical phenomenological record of the Balneário Camboriú coastline (blog & vlog)." />
    <meta property="og:locale" content="pt_BR" />
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <title>bUrY_+sUrF</title>
    <link rel="icon" type="image/x-icon" href="css/img/favicon.png">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        main { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 4px; }
        .nav { margin-bottom: 20px; }
        .nav a { color: #0066cc; text-decoration: none; font-size: 14px; }
        .nav a:hover { text-decoration: underline; }
        .spot-card { border: 1px solid #ddd; padding: 16px; margin-bottom: 16px; border-radius: 4px; display: flex; align-items: center; gap: 16px; }
        .spot-preview { width: 160px; height: 90px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
        .spot-preview img { width: 100%; height: 100%; object-fit: cover; }
        .spot-info { flex: 1; }
        .spot-name { font-weight: bold; margin-bottom: 8px; }
        .upload-form { display: flex; gap: 8px; align-items: center; }
        .upload-form input[type="file"] { padding: 4px; flex: 1; }
        .upload-form button { padding: 6px 12px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .upload-form button:hover { background: #0052a3; }
        .upload-status { margin-top: 8px; font-size: 12px; color: #665; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .blog-form { margin-bottom: 24px; border: 1px solid #ddd; padding: 18px; border-radius: 6px; background: #fafafa; }
        .blog-form h3 { margin-top: 0; }
        .blog-form label { display: block; margin-bottom: 12px; font-size: 14px; }
        .blog-form input[type="text"], .blog-form textarea, .blog-form input[type="file"] { width: 100%; box-sizing: border-box; margin-top: 6px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .blog-form textarea { min-height: 120px; resize: vertical; }
        .blog-form button { padding: 10px 16px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .blog-form button:hover { background: #0052a3; }
        .admin-tabs { display: flex; gap: 8px; margin: 20px 0; border-bottom: 1px solid #ddd; }
        .admin-tab { padding: 10px 14px; border: 1px solid #ddd; border-bottom: none; border-radius: 4px 4px 0 0; background: #f5f5f5; color: #555; cursor: pointer; }
        .admin-tab.active { background: #0066cc; border-color: #0066cc; color: white; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        body { background: #000; color: #fff; }
        main { background: #000; }
        .spot-card { border-color: #fff; }
        .spot-preview { background: #000; }
        .condition-edit { background: #000 !important; border-top-color: #fff !important; }
        .blog-form { background: #000; border-color: #fff; }
        .blog-form input[type="text"], .blog-form textarea, .blog-form input[type="file"] { background: #000; color: #fff; border-color: #fff; }
        .admin-tabs { border-bottom-color: #fff; }
        .admin-tab { background: #000; color: #fff; border-color: #fff; }
        #send-newsletter-btn { color: #fff !important; }
    </style>
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
<body>
    <main>
        <h2>Administrador</h2>
        <div style="margin-bottom: 20px;">
            <a href="api/logout.php" style="color: #dc3545; text-decoration: none;">Logout</a>
        </div>

        <div class="admin-tabs" role="tablist" aria-label="Seções do administrador">
            <button class="admin-tab active" type="button" role="tab" aria-selected="true" aria-controls="spots-tab" data-tab="spots-tab">Spots</button>
            <button class="admin-tab" type="button" role="tab" aria-selected="false" aria-controls="blog-tab" data-tab="blog-tab">Blog</button>
            <button class="admin-tab" type="button" role="tab" aria-selected="false" aria-controls="newsletter-tab" data-tab="newsletter-tab">Newsletter</button>
        </div>

        <section id="spots-tab" class="tab-panel active" role="tabpanel">
        <?php foreach ($spots as $spot): 
            $cond = [
                'wave_size' => $spot['wave_size'] ?? '',
                'wave_formation' => $spot['wave_formation'] ?? '',
                'weather' => $spot['weather'] ?? '',
                'wind' => $spot['wind'] ?? '',
                'water_temperature' => $spot['water_temperature'] ?? ''
            ];
        ?>
            <div class="spot-card">
                <div class="spot-preview">
                    <?php if (!empty($spot['image'])): ?>
                        <img src="./upload_spots/<?php echo htmlspecialchars($spot['image']); ?>" alt="<?php echo htmlspecialchars($spot['name']); ?>" />
                    <?php else: ?>
                        <small>Sem imagem</small>
                    <?php endif; ?>
                </div>
                <div class="spot-info">
                    <div class="spot-name"><?php echo htmlspecialchars($spot['name']); ?></div>
                    <div class="upload-form">
                        <input type="file" class="img-input" data-spot-id="<?php echo intval($spot['id']); ?>" accept="image/*" />
                        <button type="button" class="upload-btn" data-spot-id="<?php echo intval($spot['id']); ?>">Enviar</button>
                    </div>
                    <div class="upload-status" data-spot-id="<?php echo intval($spot['id']); ?>"></div>
                </div>
                <div class="condition-edit" style="padding:10px 14px;border-top:1px solid #eee;background:#fafafa;">
                    <button class="toggle-edit" data-spot-id="<?php echo $spot['id']; ?>">Editar condições</button>
                    <div class="edit-form" id="edit-form-<?php echo $spot['id']; ?>" style="display:none;margin-top:10px;">
                        <label>Wave size: <input type="text" name="wave_size" value="<?php echo htmlspecialchars($cond['wave_size'] ?? ''); ?>"data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <label>Formation: <input type="text" name="wave_formation" value="<?php echo htmlspecialchars($cond['wave_formation'] ?? ''); ?>" data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <label>Weather: <input type="text" name="weather" value="<?php echo htmlspecialchars($cond['weather'] ?? ''); ?>" data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <label>Wind: <input type="text" name="wind" value="<?php echo htmlspecialchars($cond['wind'] ?? ''); ?>" data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <label>Water temp: <input type="text" name="water_temperature" value="<?php echo htmlspecialchars($cond['water_temperature'] ?? ''); ?>" data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <div style="margin-top:8px;">
                            <button class="save-condition" data-spot-id="<?php echo $spot['id']; ?>">Salvar</button>
                            <span class="save-status" data-spot-id="<?php echo $spot['id']; ?>" style="margin-left:8px;color:#666;font-size:0.9em"></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
            </section>

            <section id="blog-tab" class="tab-panel" role="tabpanel">
        <div class="blog-form">
            <h3>Enviar novo post para blog</h3>
            <form id="blog-post-form">
                <label>Título
                    <input type="text" name="title" required />
                </label>
                <label>Texto
                    <textarea name="content" required></textarea>
                </label>
                <label>Imagem
                    <input type="file" name="image" accept="image/*" required />
                </label>
                <button type="submit">Enviar post</button>
                <div class="upload-status" id="blog-post-status"></div>
            </form>
        </div>
        </section>

        <section id="newsletter-tab" class="tab-panel" role="tabpanel">
        <div class="blog-form" style="margin-top:18px;">
            <h3>Enviar newsletter</h3>
            <p>Envie um aviso simples por e-mail para todos os inscritos quando as fotos do dia estiverem prontas.</p>
            <label>
                Assunto
                <input type="text" id="newsletter-subject" value="Novas fotos do dia no bUrY_+sUrF" />
            </label>
            <label>
                Mensagem (texto simples)
                <textarea id="newsletter-body">Há novas fotos do dia no site. Visite para ver as atualizações.</textarea>
            </label>
            <div style="margin-top:8px; display:flex; gap:8px; align-items:center;">
                <button id="send-newsletter-btn" type="button" style="background:#32CD32;color:#000;border:none;padding:10px 14px;border-radius:4px;cursor:pointer;font-weight:bold;">Enviar newsletter</button>
                <div class="upload-status" id="newsletter-send-status"></div>
            </div>
        </div>
        </section>
    </main>
    <footer>
        <div id="copyright">
            <p>Copyright &copy; 2026 <span style="color:#00FF00;">bUrY_+sUrF</span>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.admin-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const targetId = tab.getAttribute('data-tab');

                document.querySelectorAll('.admin-tab').forEach(item => {
                    const isActive = item === tab;
                    item.classList.toggle('active', isActive);
                    item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                document.querySelectorAll('.tab-panel').forEach(panel => {
                    panel.classList.toggle('active', panel.id === targetId);
                });
            });
        });

        document.querySelectorAll('.upload-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const spotId = btn.getAttribute('data-spot-id');
                const fileInput = document.querySelector(`.img-input[data-spot-id="${spotId}"]`);
                const statusDiv = document.querySelector(`.upload-status[data-spot-id="${spotId}"]`);

                if (!fileInput.files.length) {
                    statusDiv.textContent = 'Selecione uma imagem';
                    statusDiv.className = 'upload-status status-error';
                    return;
                }

                const formData = new FormData();
                formData.append('spot_id', spotId);
                formData.append('image', fileInput.files[0]);

                statusDiv.textContent = 'Enviando...';
                statusDiv.className = 'upload-status';

                try {
                    const res = await fetch('./api/upload_image.php', { method: 'POST', body: formData });
                    const json = await res.json();

                    if (json.status === 'ok') {
                        statusDiv.textContent = '✓ Imagem enviada com sucesso!';
                        statusDiv.className = 'upload-status status-ok';
                        fileInput.value = '';
                        // Reload preview after 1s
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        statusDiv.textContent = 'Erro: ' + (json.error || 'Desconhecido');
                        statusDiv.className = 'upload-status status-error';
                    }
                } catch (err) {
                    statusDiv.textContent = 'Erro de rede: ' + err.message;
                    statusDiv.className = 'upload-status status-error';
                }
            });
        });

        document.querySelectorAll('.toggle-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const spotId = btn.getAttribute('data-spot-id');
                const formDiv = document.getElementById(`edit-form-${spotId}`);
                formDiv.style.display = formDiv.style.display === 'none' ? 'block' : 'none';
            });
        });

        document.querySelectorAll('.save-condition').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const spotId = btn.getAttribute('data-spot-id');
                const statusSpan = document.querySelector(`.save-status[data-spot-id="${spotId}"]`);
                const inputs = document.querySelectorAll(`input[type="text"][data-spot-id="${spotId}"]`);

                const data = {
                    spot_id: parseInt(spotId),
                    condition_date: new Date().toISOString().split('T')[0], // YYYY-MM-DD
                    wave_size: inputs[0].value,
                    wave_formation: inputs[1].value,
                    weather: inputs[2].value,
                    wind: inputs[3].value,
                    water_temperature: inputs[4].value
                };

                statusSpan.textContent = 'Salvando...';
                statusSpan.style.color = '#665';

                try {
                    const res = await fetch('./api/update_conditions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const json = await res.json();

                    if (json.success) {
                        statusSpan.textContent = '✓ Salvo com sucesso!';
                        statusSpan.style.color = '#28a745';
                    } else {
                        statusSpan.textContent = 'Erro: ' + (json.error || 'Desconhecido');
                        statusSpan.style.color = '#dc3545';
                    }
                } catch (err) {
                    statusSpan.textContent = 'Erro de rede: ' + err.message;
                    statusSpan.style.color = '#dc3545';
                }
            });
        });

        const blogForm = document.getElementById('blog-post-form');
        const blogStatus = document.getElementById('blog-post-status');

        if (blogForm) {
            blogForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(blogForm);
                blogStatus.textContent = 'Enviando post...';
                blogStatus.className = 'upload-status';

                try {
                    const response = await fetch('./api/add_blog_post.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'ok') {
                        blogStatus.textContent = '✓ Post publicado com sucesso!';
                        blogStatus.className = 'upload-status status-ok';
                        blogForm.reset();
                    } else {
                        blogStatus.textContent = 'Erro: ' + (result.error || 'Falha ao publicar');
                        blogStatus.className = 'upload-status status-error';
                    }
                } catch (error) {
                    blogStatus.textContent = 'Erro de rede: ' + error.message;
                    blogStatus.className = 'upload-status status-error';
                }
            });
        }

        // Newsletter send button
        const sendBtn = document.getElementById('send-newsletter-btn');
        const sendStatus = document.getElementById('newsletter-send-status');
        if (sendBtn) {
            sendBtn.addEventListener('click', async () => {
                if (!confirm('Enviar newsletter para todos os inscritos agora?')) return;
                const subject = document.getElementById('newsletter-subject').value || '';
                const body = document.getElementById('newsletter-body').value || '';
                
                if (!subject.trim() || !body.trim()) {
                    sendStatus.textContent = 'Erro: Assunto e mensagem são obrigatórios';
                    sendStatus.className = 'upload-status status-error';
                    return;
                }
                
                sendStatus.textContent = 'Enviando...';
                sendStatus.className = 'upload-status';
                
                try {
                    const params = new URLSearchParams();
                    params.append('subject', subject);
                    params.append('body', body);
                    
                    const res = await fetch('./api/newsletter_send.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: params.toString(),
                        credentials: 'same-origin'
                    });
                    
                    const text = await res.text();
                    console.log('Response status:', res.status);
                    console.log('Response text:', text);
                    
                    if (!text || text.trim() === '') {
                        throw new Error('Servidor retornou resposta vazia (status: ' + res.status + ')');
                    }
                    
                    let j;
                    try {
                        j = JSON.parse(text);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response was:', text);
                        throw new Error('Resposta inválida do servidor: ' + text.substring(0, 100));
                    }
                    
                    if (j && j.success) {
                        sendStatus.textContent = `✓ Enviado: ${j.sent}, Falhas: ${j.failed}`;
                        sendStatus.className = 'upload-status status-ok';
                    } else {
                        const errorMsg = j.error || 'Falha desconhecida';
                        console.error('Server returned error:', j);
                        sendStatus.textContent = 'Erro: ' + errorMsg;
                        sendStatus.className = 'upload-status status-error';
                    }
                } catch (err) {
                    console.error('Error:', err);
                    sendStatus.textContent = 'Erro: ' + err.message;
                    sendStatus.className = 'upload-status status-error';
                }
            });
        }
    </script>
</body>
</html>
