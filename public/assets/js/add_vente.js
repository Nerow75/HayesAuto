function updateRepairPrice(select) {
  const selectedOption = select.options[select.selectedIndex];
  const sellPrice = selectedOption.getAttribute("data-price-sell");

  // Préremplir le champ "Tarif" avec le prix de vente
  document.getElementById("repair_price").value = sellPrice;
  document.getElementById("tarif").value = sellPrice; // Préremplir mais modifiable
}

function filterVehicules() {
  const input = document.getElementById("search_modele").value.toLowerCase();
  const select = document.getElementById("modele_vehicule");
  const options = select.options;

  for (let i = 0; i < options.length; i++) {
    const optionText = options[i].textContent.toLowerCase();
    if (optionText.includes(input)) {
      options[i].style.display = ""; // Afficher l'option
    } else {
      options[i].style.display = "none"; // Masquer l'option
    }
  }
}

// Activer Select2 sur le champ "modele_vehicule"
$(document).ready(function () {
  $("#modele_vehicule").select2({
    placeholder: "-- Sélectionnez un modèle --",
    allowClear: true,
  });
});
