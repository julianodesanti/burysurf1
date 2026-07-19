// Função para forçar reload apenas uma vez por sessão
(function() {
  if (!sessionStorage.getItem("reloaded")) {
    sessionStorage.setItem("reloaded", "true");
    window.location.reload();
  }
})();
// Data
	const diasDaSemana = ["domingo", "segunda-feira", "terça-feira", "quarta-feira", "quinta-feira", "sexta-feira", "sábado"];
	const meses = ["janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"];
	const dataAtual = new Date();
	const diaDaSemana = diasDaSemana[dataAtual.getDay()];
	const diaDoMes = dataAtual.getDate();
	const mes = meses[dataAtual.getMonth()];
	const dataFormatada = `${diaDaSemana}, ${diaDoMes} de ${mes}`;
	document.getElementById("data").textContent = dataFormatada;
// Menubar
function linkhome() {
	window.location.href = "index.php";
}
function linkblog() {
	window.location.href = "blog.php";
}
function linkvlog() {
	window.location.href = "vlog.html";
}
function linksobre() {
	window.location.href = "sobre.html";
}
// Popup functions removed - content now served dynamically from index.php
// Link
function linkyoutube() {
    window.open('https://youtube.com/@ocabreiro', '_blank');
}
function linktwitch() {
    window.open('https://twitch.com/burysurf', '_blank');
}
function linkfacebook() {
    window.open('https://www.facebook.com/profile.php?id=61568955166072', '_blank');
}
function linkinstagram() {
    window.open('https://instagram.com/burysurf', '_blank');
}
function linkemail() {
    window.open('mailto:contato@burysurf.com', '_blank');
}

// ============================================
// DATABASE - Load Surf Conditions
// ============================================

// Initialize database helper
const db = new BurySurfDB('/api/');

/**
 * Load and display current surf conditions from database
 */
async function loadSurfConditions() {
	try {
		const conditions = await db.getAllConditions();
		
		if (!conditions || conditions.length === 0) {
			console.warn('No conditions found in database');
			return;
		}
		
		updateGridDisplay(conditions);
		
	} catch (error) {
		console.error('Error loading conditions:', error);
	}
}

/**
 * Update grid items with condition data
 */
function updateGridDisplay(conditions) {
	const grids = document.querySelectorAll('#grid');
	
	conditions.forEach((spot, index) => {
		// Each spot has 2 grids: title grid + data grid
		const dataGridIndex = (index * 2) + 1;
		
		if (dataGridIndex < grids.length) {
			const gridItems = grids[dataGridIndex].querySelectorAll('#grid-item');
			
			// Update grid items (indices: 2=size, 5=formation, 8=weather, 11=wind, 14=temp)
			if (gridItems.length >= 15) {
				gridItems[2].textContent = spot.wave_size || '-';
				gridItems[5].textContent = spot.wave_formation || '-';
				gridItems[8].textContent = spot.weather || '-';
				gridItems[11].textContent = spot.wind || '-';
				gridItems[14].textContent = spot.water_temperature || '-';
			}
		}
	});
}

// Load conditions when page DOM is ready
document.addEventListener('DOMContentLoaded', loadSurfConditions);

// Cookie helper functions
function setCookie(name, value, days) {
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (encodeURIComponent(value) || "") + expires + "; path=/";
}

function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) === ' ') c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
	}
	return null;
}

function deleteCookie(name) {
	setCookie(name, "", -1);
}

// Consent banner
function createCookieConsentBanner() {
	if (getCookie('cookie_consent')) return;
	var banner = document.createElement('div');
	banner.id = 'cookie-consent';
	banner.className = 'cookie-consent';
	banner.innerHTML = '<div class="cookie-consent-inner">Você aceita surfar nestes cookies?<div class="cookie-actions"><button id="accept-cookies">Aceitar</button><button id="decline-cookies">Recusar</button></div></div>';
	document.body.appendChild(banner);

	document.getElementById('accept-cookies').addEventListener('click', function () {
		setCookie('cookie_consent', 'accepted', 365);
		banner.remove();
	});

	document.getElementById('decline-cookies').addEventListener('click', function () {
		setCookie('cookie_consent', 'declined', 365);
		banner.remove();
	});
}

function initCookieConsent() {
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', createCookieConsentBanner);
	} else {
		createCookieConsentBanner();
	}
}

initCookieConsent();