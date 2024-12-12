document.getElementById('active-button').addEventListener('click', function () {
    const button = this;
    const userId = button.getAttribute('data-user-id');
    const currentStatus = button.getAttribute('data-active-status');

    // Déterminer le nouvel état
    const newStatus = currentStatus === 'Yes' ? 'No' : 'Yes';

    // Faire une requête AJAX pour mettre à jour l'état
    fetch('update_active.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: userId, active: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour l'état visuellement
            button.setAttribute('data-active-status', newStatus);
            button.classList.toggle('error', newStatus === 'No');
            button.classList.toggle('success', newStatus === 'Yes');

            // Mettre à jour le texte du bouton
            const link = button.querySelector('a');
            link.textContent = newStatus === 'Yes' ? 'Compte Actif' : 'Compte Inactif';
        } else {
            alert('Erreur lors de la mise à jour du statut : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur réseau :', error);
        alert('Une erreur réseau est survenue.');
    });
});