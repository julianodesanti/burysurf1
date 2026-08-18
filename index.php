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
    <style>
    /* Newsletter modal styles */
    #ns-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 9999; }
    #ns-modal { background: #0b0b0b; border: 2px solid #32CD32; color: #fff; padding: 18px; width: 320px; max-width: 90%; border-radius: 8px; font-family: 'Inconsolata', monospace; }
    #ns-modal h3 { margin: 0 0 8px 0; font-family: papyrus, sans-serif; color: #00FF00; }
    #ns-modal .ns-close { position: absolute; right: 18px; top: 12px; background: transparent; border: none; color: #fff; font-size: 18px; cursor: pointer; }
    #ns-modal form { display:flex; gap:8px; align-items:center; }
    #ns-modal input[type="email"] { flex:1; padding:8px; border-radius:4px; border:1px solid #444; background:#111; color:#fff; }
    #ns-modal button.ns-submit { background:#32CD32; color:#000; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:bold; }
    #ns-msg { margin-top:10px; font-size:13px; }
    </style>
    <title>bUrY_+sUrF</title>
    <link rel="icon" type="image/x-icon" href="css/img/favicon.png">
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
    
    <!-- Newsletter Modal -->
    <div id="ns-overlay" aria-hidden="true">
        <div id="ns-modal">
            <button class="ns-close" aria-label="Fechar">&times;</button>
            <h3>Newsletter</h3>
            <form id="ns-form">
                <input type="email" id="ns-email" placeholder="seu@email.com" required />
                <button type="submit" class="ns-submit">Inscrever</button>
            </form>
            <div id="ns-msg"></div>
        </div>
    </div>

    <footer>
        <div id="copyright">
            <p>Copyright &copy; 2026 <span style="color:#00FF00;">bUrY_+sUrF</span>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script type="text/javascript" src="./js/BurySurfDB.js"></script>
    <script type="text/javascript" src="./js/main.js"></script>
    <script>
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
    <script>
    // Newsletter modal logic
    (function(){
        const overlay = document.getElementById('ns-overlay');
        const closeBtn = document.querySelector('#ns-modal .ns-close');
        const form = document.getElementById('ns-form');
        const emailInput = document.getElementById('ns-email');
        const msg = document.getElementById('ns-msg');

        function showModal(){
            overlay.style.display = 'flex';
            overlay.setAttribute('aria-hidden','false');
        }
        function hideModal(){
            overlay.style.display = 'none';
            overlay.setAttribute('aria-hidden','true');
        }

        closeBtn.addEventListener('click', hideModal);
        overlay.addEventListener('click', function(e){ if (e.target === overlay) hideModal(); });

        form.addEventListener('submit', async function(e){
            e.preventDefault();
            msg.textContent = 'Enviando...';
            const fd = new FormData(); fd.append('email', emailInput.value.trim());
            try {
                const res = await fetch('./api/newsletter_subscribe.php', { method: 'POST', body: fd });
                const j = await res.json();
                if (j && j.success) {
                    msg.textContent = 'Obrigado — verifique seu e-mail.';
                    setTimeout(hideModal, 1200);
                } else {
                    msg.textContent = 'Erro: ' + (j.error || 'Falha ao assinar');
                }
            } catch (err) {
                msg.textContent = 'Erro de rede';
            }
        });

        // show modal on first load
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(showModal, 800); });
    })();
    </script>
</body>
</html>