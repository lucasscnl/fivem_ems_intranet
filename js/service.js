document.getElementById('service-button').addEventListener('click', function () {
    const button = this;
    const userId = button.getAttribute('data-user-id');
    const currentStatus = button.getAttribute('data-service-status');

    // Déterminer le nouvel état
    const newStatus = currentStatus === 'Yes' ? 'No' : 'Yes';

    // Désactiver temporairement le bouton pour éviter plusieurs clics rapides
    button.disabled = true;

    // Faire une requête AJAX pour changer l'état
    fetch('update_service.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId, service: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour l'état visuellement
            button.setAttribute('data-service-status', newStatus);
            button.classList.toggle('error', newStatus === 'Yes');
            button.classList.toggle('success', newStatus === 'No');

            // Mettre à jour le texte du bouton
            const link = button.querySelector('a');
            link.textContent = newStatus === 'Yes' ? 'Stopper son service' : 'Prendre son service';

            // Rafraîchir la page après un court délai pour éviter des erreurs visuelles
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert('Erreur lors de la mise à jour du service : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur AJAX :', error);
        alert('Une erreur est survenue. Veuillez réessayer.');
    })
    .finally(() => {
        // Réactiver le bouton après traitement
        button.disabled = false;
    });
});

setInterval(() => {
    fetch('update_prime.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Mise à jour réussie :', data.message);
            } else {
                console.error('Erreur de mise à jour :', data.message);
            }
        })
        .catch(error => console.error('Erreur : ', error));
}, 60000);

