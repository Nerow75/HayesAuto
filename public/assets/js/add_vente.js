function updateRepairPrice(select) {
  let totalPrice = 0;

  // Si un modèle est sélectionné, on prend son prix
  if (select.selectedIndex > 0) {
    const selectedOption = select.options[select.selectedIndex];
    const sellPrice =
      parseFloat(selectedOption.getAttribute("data-price-sell")) || 0;
    totalPrice += sellPrice;
  }

  // Ajouter le prix des options de révision sélectionnées
  document
    .querySelectorAll('input[name="revision_items[]"]:checked')
    .forEach(function (checkbox) {
      totalPrice += parseFloat(checkbox.getAttribute("data-price")) || 0;
    });

  // Si rien n'est sélectionné, on vide le champ
  document.getElementById("tarif").value = totalPrice > 0 ? totalPrice : "";
}

// Ajouter un écouteur d'événement pour les checkboxes
document
  .querySelectorAll('input[name="revision_items[]"]')
  .forEach(function (checkbox) {
    checkbox.addEventListener("change", function () {
      const select = document.getElementById("modele_vehicule");
      updateRepairPrice(select);
    });
  });

// Ajouter un écouteur d'événement pour le select modèle
document
  .getElementById("modele_vehicule")
  .addEventListener("change", function () {
    updateRepairPrice(this);
  });

// Activer Select2 sur le champ "modele_vehicule"
$(document).ready(function () {
  $("#modele_vehicule").select2({
    placeholder: "-- Sélectionnez un modèle --",
    allowClear: true,
  });
});
