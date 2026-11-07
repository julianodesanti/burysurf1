<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/api/check_auth.php';
require_once __DIR__ . '/api/db_config.php';

if (empty($_SESSION['newsletter_csrf'])) {
    $_SESSION['newsletter_csrf'] = bin2hex(random_bytes(32));
}
$newsletterCsrf = $_SESSION['newsletter_csrf'];

$spots = [];
$sql = "SELECT ss.spot_id as id, ss.spot_name as name, ss.image, sc.wave_size, sc.wave_formation, sc.weather, sc.wind, sc.water_temperature FROM surf_spots ss LEFT JOIN surf_conditions sc ON ss.spot_id = sc.spot_id AND sc.condition_date = CURDATE() ORDER BY ss.spot_id";
    $res = $conn->query($sql);
        if ($res) {while ($r = $res->fetch_assoc()) $spots[] = $r;}

$subscribers = [];
$newsletterError = false;
$newsletterResult = $conn->query("SELECT email, created_at FROM newsletter_subscribers ORDER BY created_at DESC");
if ($newsletterResult) {
    while ($subscriber = $newsletterResult->fetch_assoc()) {
        $subscribers[] = $subscriber;
    }
    $newsletterResult->free();
} else {
    error_log('Newsletter subscriber query failed: ' . $conn->error);
    $newsletterError = true;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - bUrY_+sUrF</title>
    <link rel="stylesheet" href="./css/style.css" />
    <style>
        body.admin-page { font-family: "Trebuchet MS", Verdana, sans-serif; padding: 20px; background: #000000; color: #e7eee8; }
        body.admin-page main { max-width: 1080px; margin: 0 auto; background: #000000; padding: 24px; border: 1px solid #29352b; border-radius: 8px; box-shadow: 0 12px 36px rgba(0, 0, 0, .28); }
        .admin-page h2, .admin-page h3 { color: #f1f7f1; }
        .nav { margin-bottom: 20px; }
        .nav a { color: #00ef55; text-decoration: none; font-size: 14px; }
        .nav a:hover { text-decoration: underline; }
        .spot-card { border: 1px solid #00ff19; padding: 16px; margin-bottom: 16px; border-radius: 6px; display: flex; align-items: center; gap: 16px; background: #000000; }
        .spot-preview { width: 160px; height: 90px; background: #141a15; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; color: #9eaa9f; }
        .spot-preview img { width: 100%; height: 100%; object-fit: cover; }
        .spot-info { flex: 1; }
        .spot-name { font-weight: bold; margin-bottom: 8px; }
        .upload-form { display: flex; gap: 8px; align-items: center; }
        .upload-form input[type="file"] { padding: 4px; flex: 1; }
        .admin-page button { font-family: inherit; }
        .admin-page button:not(.admin-tab) { padding: 8px 12px; border: 0; border-radius: 4px; background: #0040ff; color: #ffffff; cursor: pointer; font-weight: bold; }
        .upload-form button, .blog-form button, .newsletter-setup, .newsletter-form button { padding: 9px 14px; background: #00d64f; color: #07100a; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .admin-page button:hover { background: #00f45a; }
        .admin-page button:disabled { opacity: .6; cursor: wait; }
        .upload-status { margin-top: 8px; font-size: 12px; color: #abb5ac; }
        .status-ok { color: #45e879; }
        .status-error { color: #ff7373; }
        .blog-form { margin-bottom: 24px; border: 1px solid #303a31; padding: 18px; border-radius: 6px; background: #202720; }
        .blog-form h3 { margin-top: 0; }
        .blog-form label { display: block; margin-bottom: 12px; font-size: 14px; }
        .admin-page input:not([type="file"]), .admin-page textarea, .admin-page select { width: 100%; box-sizing: border-box; margin-top: 6px; padding: 10px; border: 1px solid #3b493d; border-radius: 4px; background: #111713; color: #e7eee8; font: inherit; }
        .blog-form textarea { min-height: 120px; resize: vertical; }
        .admin-page input[type="file"] { color: #d5dfd6; }
        .admin-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin: 20px 0; border-bottom: 1px solid #344035; }
        .admin-tab { padding: 10px 16px; border: 1px solid #344035; border-bottom: 0; border-radius: 5px 5px 0 0; background: #202720; color: #c6d0c7; cursor: pointer; font-size: 14px; }
        .admin-tab[aria-selected="true"] { background: #00d64f; color: #07100a; border-color: #00d64f; font-weight: bold; }
        .tab-panel[hidden] { display: none; }
        .condition-edit { padding: 10px 14px; border: 1px solid #303a31; background: #171e18; border-radius: 5px; }
        .save-status { color: #abb5ac; }
        .newsletter-form { max-width: 760px; padding: 20px; border: 1px solid #303a31; border-radius: 6px; background: #202720; }
        .newsletter-form label { display: block; margin: 14px 0; }
        .newsletter-form textarea { min-height: 220px; resize: vertical; line-height: 1.5; }
        .newsletter-form-status { min-height: 1.5em; margin-top: 12px; }
        .newsletter-table-wrap { overflow-x: auto; }
        .newsletter-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .newsletter-table th, .newsletter-table td { padding: 10px; border: 1px solid #344035; text-align: left; }
        .newsletter-table th { background: #202720; color: #00ed55; }
        .newsletter-table tr:nth-child(even) { background: #171e18; }
        .newsletter-alert { padding: 12px; border-radius: 4px; border: 1px solid #665a25; background: #302b18; color: #f2dc83; }
        .newsletter-setup { margin: 12px 0; }
        @media (max-width: 600px) {
            .admin-tab { flex: 1 1 auto; }
            .newsletter-table th, .newsletter-table td { padding: 7px; overflow-wrap: anywhere; }
            .spot-card { flex-direction: column; align-items: stretch; }
            .spot-preview { width: 100%; height: auto; aspect-ratio: 16 / 9; }
        }
        @media (max-width: 360px) {
            body.admin-page { padding: 8px; }
            body.admin-page main { padding: 12px; }
            .admin-tab { padding: 9px 8px; font-size: 12px; }
        }
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
<body class="admin-page">
    <main>
        <h2>Administrador</h2>
        <div style="margin-bottom: 20px;">
            <a href="api/logout.php" style="color: #dc3545; text-decoration: none;">Logout</a>
        </div>

        <div class="admin-tabs" role="tablist" aria-label="Funções do administrador">
            <button type="button" class="admin-tab" id="tab-button-spots" role="tab" aria-selected="true" aria-controls="tab-spots" data-tab="tab-spots">Praias e condições</button>
            <button type="button" class="admin-tab" id="tab-button-blog" role="tab" aria-selected="false" aria-controls="tab-blog" data-tab="tab-blog">Blog</button>
            <button type="button" class="admin-tab" id="tab-button-newsletter" role="tab" aria-selected="false" aria-controls="tab-newsletter" data-tab="tab-newsletter">Disparar newsletter</button>
            <button type="button" class="admin-tab" id="tab-button-inscritos" role="tab" aria-selected="false" aria-controls="tab-inscritos" data-tab="tab-inscritos">Inscritos</button>
        </div>

        <section class="tab-panel" id="tab-spots" role="tabpanel" aria-labelledby="tab-button-spots">
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
                <div class="condition-edit">
                    <button class="toggle-edit" data-spot-id="<?php echo $spot['id']; ?>">Editar condições</button>
                    <div class="edit-form" id="edit-form-<?php echo $spot['id']; ?>" style="display:none;margin-top:10px;">
                        <label>Wave size: <input type="text" name="wave_size" value="<?php echo htmlspecialchars($cond['wave_size'] ?? ''); ?>"data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <label>Formation: <input type="text" name="wave_formation" value="<?php echo htmlspecialchars($cond['wave_formation'] ?? ''); ?>" data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <label>Weather: <input type="text" name="weather" value="<?php echo htmlspecialchars($cond['weather'] ?? ''); ?>" data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <label>Wind: <input type="text" name="wind" value="<?php echo htmlspecialchars($cond['wind'] ?? ''); ?>" data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <label>Water temp: <input type="text" name="water_temperature" value="<?php echo htmlspecialchars($cond['water_temperature'] ?? ''); ?>" data-spot-id="<?php echo $spot['id']; ?>" /></label>
                        <div style="margin-top:8px;">
                            <button class="save-condition" data-spot-id="<?php echo $spot['id']; ?>">Salvar</button>
                            <span class="save-status" data-spot-id="<?php echo $spot['id']; ?>" style="margin-left:8px;font-size:0.9em"></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </section>

        <section class="tab-panel" id="tab-blog" role="tabpanel" aria-labelledby="tab-button-blog" hidden>
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

        <section class="tab-panel" id="tab-newsletter" role="tabpanel" aria-labelledby="tab-button-newsletter" hidden>
            <h3>Compor newsletter</h3>
            <p><?php echo count($subscribers); ?> inscritos. O assunto e o corpo serão enviados individualmente a cada endereço.</p>
            <form id="newsletter-send-form" class="newsletter-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($newsletterCsrf, ENT_QUOTES, 'UTF-8'); ?>" />
                <label>Assunto
                    <input type="text" name="subject" required maxlength="200" />
                </label>
                <label>Corpo do e-mail
                    <textarea name="body" required maxlength="100000"></textarea>
                </label>
                <button type="submit">Enviar para todos os inscritos</button>
                <p id="newsletter-send-status" class="newsletter-form-status" role="status" aria-live="polite"></p>
            </form>
        </section>

        <section class="tab-panel" id="tab-inscritos" role="tabpanel" aria-labelledby="tab-button-inscritos" hidden>
            <h3>Contatos inscritos</h3>
            <?php if ($newsletterError): ?>
                <div class="newsletter-alert">
                    A lista de contatos não está disponível ou a estrutura ainda precisa ser preparada:
                    <form method="post" action="./api/migrate_newsletter.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($newsletterCsrf, ENT_QUOTES, 'UTF-8'); ?>" />
                        <button type="submit" class="newsletter-setup">Preparar/atualizar banco da newsletter</button>
                    </form>
                </div>
            <?php elseif (count($subscribers) === 0): ?>
                <p>Ainda não há contatos inscritos.</p>
            <?php else: ?>
                <p>Total de inscritos: <?php echo count($subscribers); ?></p>
                <div class="newsletter-table-wrap">
                <table class="newsletter-table">
                    <thead>
                        <tr><th>E-mail</th><th>Data da inscrição</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subscriber['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php
                                    $subscribedAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $subscriber['created_at']);
                                    echo htmlspecialchars($subscribedAt ? $subscribedAt->format('d/m/Y H:i') : $subscriber['created_at'], ENT_QUOTES, 'UTF-8');
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
            <form method="post" action="./api/migrate_newsletter.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($newsletterCsrf, ENT_QUOTES, 'UTF-8'); ?>" />
                <button type="submit" class="newsletter-setup">Preparar/atualizar banco da newsletter</button>
            </form>
        </section>
    </main>
    <footer>
		<div id="copyright">
			<p>
				Copyright &copy; 2026 <span style="color:#00FF00;">bUrY_+sUrF</span>. Todos os direitos reservados.
			</p>
		</div>
	</footer>

    <script>
        const adminTabs = Array.from(document.querySelectorAll('.admin-tab'));
        const tabPanels = Array.from(document.querySelectorAll('.tab-panel'));

        function activateTab(tabId) {
            adminTabs.forEach(tab => {
                const selected = tab.dataset.tab === tabId;
                tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
            tabPanels.forEach(panel => {
                panel.hidden = panel.id !== tabId;
            });
        }

        adminTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                activateTab(tab.dataset.tab);
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab.dataset.tab.replace('tab-', ''));
                window.history.replaceState(null, '', url);
            });
        });

        const requestedTab = new URLSearchParams(window.location.search).get('tab');
        if (requestedTab) {
            const requestedPanel = document.getElementById('tab-' + requestedTab);
            if (requestedPanel) activateTab(requestedPanel.id);
        }

        const newsletterSendForm = document.getElementById('newsletter-send-form');
        newsletterSendForm.addEventListener('submit', async event => {
            event.preventDefault();
            if (!window.confirm('Enviar esta newsletter para todos os inscritos?')) return;

            const submitButton = newsletterSendForm.querySelector('button[type="submit"]');
            const status = document.getElementById('newsletter-send-status');
            submitButton.disabled = true;
            status.textContent = 'Enviando newsletter...';
            status.className = 'newsletter-form-status';

            try {
                const response = await fetch('./api/send_newsletter.php', {
                    method: 'POST',
                    body: new FormData(newsletterSendForm)
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    const partial = result.sent !== undefined
                        ? ' Enviados: ' + result.sent + '; com falha: ' + result.failed + '.'
                        : '';
                    throw new Error((result.error || 'Não foi possível enviar a newsletter.') + partial);
                }

                status.textContent = result.message;
                status.classList.add('status-ok');
                newsletterSendForm.reset();
                newsletterSendForm.querySelector('input[name="csrf_token"]').value = <?php echo json_encode($newsletterCsrf); ?>;
            } catch (error) {
                status.textContent = error.message || 'Erro ao enviar a newsletter.';
                status.classList.add('status-error');
            } finally {
                submitButton.disabled = false;
            }
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
    </script>
</body>
</html>
