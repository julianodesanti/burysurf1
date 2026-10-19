<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/api/db_config.php';

$spots = [];
// carregar os spots fixos
$spotSql = "SELECT spot_id AS id, spot_name AS name, image FROM surf_spots ORDER BY spot_id";
if ($sres = $conn->query($spotSql)) {
    while ($srow = $sres->fetch_assoc()) {
        $spots[] = $srow;
    }
    $sres->free();
}

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
        .newsletter-modal {
            position: fixed;
            inset: 0;
            box-sizing: border-box;
            width: min(480px, calc(100vw - 24px));
            max-height: calc(100vh - 24px);
            max-height: calc(100dvh - 24px);
            margin: auto;
            overflow-y: auto;
            padding: clamp(16px, 5vw, 32px);
            border: 1px solid #00e94f;
            border-radius: 8px;
            background: #000000;
            color: #e7eee8;
            box-shadow: 0 12px 48px rgba(0, 0, 0, .72);
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
            text-align: center;
        }
        .newsletter-modal::backdrop { background: rgba(0, 0, 0, .78); }
        .newsletter-modal h2 { margin: 0 18px 12px; color: #f3fff5; font-size: clamp(1.2rem, 5vw, 1.8rem); line-height: 1.2; }
        .newsletter-modal p { line-height: 1.55; }
        .newsletter-modal .newsletter-eyebrow { margin: 0 0 10px; color: #00ef55; font-size: .82rem; font-weight: bold; letter-spacing: .16em; text-transform: uppercase; }
        .newsletter-modal label { display: block; margin: 18px 0 6px; color: #dce9de; font-weight: bold; text-align: left; }
        .newsletter-modal input { width: 100%; box-sizing: border-box; padding: 12px; border: 1px solid #3c4d3f; border-radius: 4px; outline-color: #00e94f; background: #080d09; color: #f3fff5; font: inherit; }
        .newsletter-modal form button { width: 100%; margin-top: 12px; padding: 12px; border: 0; border-radius: 4px; background: #00d64f; color: #07100a; font: inherit; font-weight: bold; cursor: pointer; }
        .newsletter-modal form button:hover { background: #00f45a; }
        .newsletter-modal form button:disabled { opacity: .6; cursor: wait; }
        .newsletter-close { position: absolute; top: 8px; right: 10px; padding: 0 6px; border: 0; background: none; color: #c7d2c8; font: inherit; font-size: 28px; line-height: 1; cursor: pointer; }
        .newsletter-close:hover { color: #00ef55; }
        .newsletter-modal .newsletter-status { min-height: 1.4em; margin: 10px 0 0; overflow-wrap: anywhere; }
        .newsletter-status-success { color: #45e879; }
        .newsletter-status-error { color: #ff7373; }
        @media (max-width: 480px) {
            .newsletter-modal { width: calc(100vw - 20px); max-height: calc(100dvh - 20px); }
            .newsletter-modal h2 { margin-right: 10px; margin-left: 10px; }
        }
        @media (max-width: 200px) {
            .newsletter-modal { width: calc(100vw - 12px); padding: 14px 10px; }
            .newsletter-modal h2 { margin-right: 16px; margin-left: 16px; }
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
        <div id="data"></div>

        <div id="grid-container">
<?php
// load today's conditions for each spot (if present)
$conditions = [];
$condSql = "SELECT spot_id, wave_size, wave_formation, weather, wind, water_temperature, condition_date FROM surf_conditions WHERE condition_date = CURDATE()";
if ($cres = $conn->query($condSql)) {
    while ($crow = $cres->fetch_assoc()) {
        $conditions[intval($crow['spot_id'])] = $crow;
    }
    $cres->free();
}

foreach ($spots as $spot):
    $sid = intval($spot['id']);
    $cond = isset($conditions[$sid]) ? $conditions[$sid] : null;
?>
            <div id="grid">
                <div id="titulo">
                    <span>_+</span>
                </div>
                <span><?php echo htmlspecialchars($spot['name']); ?></span>
                <div id="box-<?php echo $sid; ?>" class="spot-box">
                    <div id="marcadagua"></div>
                    <?php if (!empty($spot['image'])): ?>
                        <img src="./upload_spots/<?php echo htmlspecialchars($spot['image']); ?>" alt="<?php echo htmlspecialchars($spot['name']); ?>" style="width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;" />
                    <?php endif; ?>
                </div>
            </div>

            <div id="grid">
                <div id="grid-containers">
                    <div id="grid-item">&#128207;</div>
                    <div id="grid-item">TAMANHO</div>
                    <div id="grid-item" data-field="wave_size"><?php echo htmlspecialchars($cond['wave_size'] ?? '-'); ?></div>
                    <div id="grid-item">&#127754;</div>
                    <div id="grid-item">FORMAÇÃO</div>
                    <div id="grid-item" data-field="wave_formation"><?php echo htmlspecialchars($cond['wave_formation'] ?? '-'); ?></div>
                    <div id="grid-item">&#9925;</div>
                    <div id="grid-item">TEMPO</div>
                    <div id="grid-item" data-field="weather"><?php echo htmlspecialchars($cond['weather'] ?? '-'); ?></div>
                    <div id="grid-item">&#128681;</div>
                    <div id="grid-item">VENTO</div>
                    <div id="grid-item" data-field="wind"><?php echo htmlspecialchars($cond['wind'] ?? '-'); ?></div>
                    <div id="grid-item">&#127777;</div>
                    <div id="grid-item">TEMPERATURA DA ÁGUA</div>
                    <div id="grid-item" data-field="water_temperature"><?php echo htmlspecialchars($cond['water_temperature'] ?? '-'); ?></div>
                </div>
            </div>
<?php endforeach; ?>
        </div>
    </main>
    <dialog id="newsletter-modal" class="newsletter-modal" aria-labelledby="newsletter-title">
        <button type="button" class="newsletter-close" aria-label="Fechar janela">&times;</button>
        <p class="newsletter-eyebrow">bUrY_+sUrF</p>
        <h2 id="newsletter-title">Newsletter</h2>
        <p>Cadastre seu e-mail para receber o boletim das ondas. A inscrição só será concluída depois que você confirmar pelo link enviado ao seu e-mail.</p>
        <form id="newsletter-form">
            <label for="newsletter-email">Seu e-mail</label>
            <input id="newsletter-email" type="email" name="email" autocomplete="email" required maxlength="254" placeholder="voce@exemplo.com" />
            <button type="submit">Inscrever</button>
            <p id="newsletter-status" class="newsletter-status" role="status" aria-live="polite"></p>
        </form>
    </dialog>
    <footer>
        <div id="copyright">
            <p>Copyright &copy; 2026 <span style="color:#00FF00;">bUrY_+sUrF</span>. Todos os direitos reservados.</p>
        </div>
    </footer>
    <script type="text/javascript" src="./js/BurySurfDB.js"></script>
    <script type="text/javascript" src="./js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const newsletterModal = document.getElementById('newsletter-modal');
        const newsletterForm = document.getElementById('newsletter-form');
        const newsletterStatus = document.getElementById('newsletter-status');
        const newsletterClose = document.querySelector('.newsletter-close');

        if (newsletterModal && typeof newsletterModal.showModal === 'function') {
            newsletterModal.showModal();
        }

        newsletterClose.addEventListener('click', function () {
            newsletterModal.close();
        });

        newsletterForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const submitButton = newsletterForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            newsletterStatus.textContent = 'Enviando sua inscrição...';
            newsletterStatus.className = 'newsletter-status';

            try {
                const response = await fetch('./api/subscribe_newsletter.php', {
                    method: 'POST',
                    body: new FormData(newsletterForm)
                });
                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.error || 'Não foi possível concluir sua inscrição.');
                }

                newsletterStatus.textContent = result.message;
                newsletterStatus.classList.add('newsletter-status-success');
                newsletterForm.reset();
            } catch (error) {
                newsletterStatus.textContent = error.message || 'Erro de rede ao enviar sua inscrição.';
                newsletterStatus.classList.add('newsletter-status-error');
            } finally {
                submitButton.disabled = false;
            }
        });
    });

    // Inline JS to handle edit toggles and AJAX updates to API
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toggle-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-spot-id');
                const form = document.getElementById('edit-form-' + id);
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            });
        });

        document.querySelectorAll('.save-condition').forEach(btn => {
            btn.addEventListener('click', async () => {
                const spotId = btn.getAttribute('data-spot-id');
                const form = document.getElementById('edit-form-' + spotId);
                const status = document.querySelector('.save-status[data-spot-id="' + spotId + '"]');

                const wave_size = form.querySelector('input[name="wave_size"]').value;
                const wave_formation = form.querySelector('input[name="wave_formation"]').value;
                const weather = form.querySelector('input[name="weather"]').value;
                const wind = form.querySelector('input[name="wind"]').value;
                const water_temperature = form.querySelector('input[name="water_temperature"]').value;

                status.textContent = 'Salvando...';

                try {
                    const body = {
                        spot_id: parseInt(spotId, 10),
                        condition_date: new Date().toISOString().slice(0,10),
                        wave_size: wave_size,
                        wave_formation: wave_formation,
                        weather: weather,
                        wind: wind,
                        water_temperature: water_temperature
                    };

                    const res = await fetch('./api/update_conditions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    });
                    const json = await res.json();
                    if (json && json.success) {
                        status.textContent = 'Salvo';
                        setTimeout(() => { status.textContent = ''; location.reload(); }, 800);
                    } else {
                        status.textContent = 'Erro: ' + (json.error || 'Falha');
                    }
                } catch (err) {
                    status.textContent = 'Erro de rede';
                }
            });
        });
    });
    </script>
</body>
</html>