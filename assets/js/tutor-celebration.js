/**
 * Tutor LMS Celebration Micro-interactions
 * 
 * Fires confetti when a user clicks "Mark as Complete" or passes a quiz,
 * intercepting the form submission to allow the animation to play.
 */

document.addEventListener('DOMContentLoaded', () => {
	// Never let a missing confetti lib block lesson completion: if the script
	// failed to load, skip all interception so forms submit natively.
	if (typeof confetti === 'undefined') {
		return;
	}

	// 1. Intercept "Mark as Complete" forms in Tutor LMS
	const completeForms = document.querySelectorAll('form.tutor-topbar-mark-btn');

	completeForms.forEach(form => {
		form.addEventListener('submit', (e) => {
			// Only intercept if we haven't already celebrated
			if (!form.dataset.celebrated) {
				e.preventDefault();
				
				// Fire Confetti!
				const duration = 1.5 * 1000;
				const end = Date.now() + duration;

				(function frame() {
					confetti({
						particleCount: 5,
						angle: 60,
						spread: 55,
						origin: { x: 0 },
						colors: ['#26ccff', '#a25afd', '#ff5e7e', '#88ff5a', '#fcff42', '#ffa62d', '#ff36ff']
					});
					confetti({
						particleCount: 5,
						angle: 120,
						spread: 55,
						origin: { x: 1 },
						colors: ['#26ccff', '#a25afd', '#ff5e7e', '#88ff5a', '#fcff42', '#ffa62d', '#ff36ff']
					});

					if (Date.now() < end) {
						requestAnimationFrame(frame);
					} else {
						// Submit the form after the confetti finishes
						form.dataset.celebrated = "true";
						form.submit();
					}
				}());
			}
		});
	});

	// 2. Also check if the URL has a success parameter from a previous action
	// e.g., if redirected back after completing a quiz
	const urlParams = new URLSearchParams(window.location.search);
	if (urlParams.has('tutor_lesson_completed') || urlParams.has('quiz_passed')) {
		confetti({
			particleCount: 150,
			spread: 100,
			origin: { y: 0.6 },
			zIndex: 9999
		});
		
		// Clean up the URL so it doesn't fire again on refresh
		const newUrl = window.location.pathname + window.location.search.replace(/&?(tutor_lesson_completed|quiz_passed)=[^&]*/g, '');
		window.history.replaceState({}, document.title, newUrl);
	}
});
