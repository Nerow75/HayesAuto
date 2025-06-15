function updateRepairPrice(select) {
  let totalPrice = 0;
  const onlyRevision = document.getElementById("only_revision")?.checked;

  // Si "uniquement révision" n'est PAS coché, on ajoute le prix du modèle
  if (!onlyRevision && select.selectedIndex > 0) {
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

  document.getElementById("tarif").value = totalPrice > 0 ? totalPrice : "";
}

// Ajoute l'écouteur sur la checkbox "only_revision"
document
  .getElementById("only_revision")
  ?.addEventListener("change", function () {
    const select = document.getElementById("modele_vehicule");
    updateRepairPrice(select);
  });

// Les autres écouteurs restent inchangés
document
  .querySelectorAll('input[name="revision_items[]"]')
  .forEach(function (checkbox) {
    checkbox.addEventListener("change", function () {
      const select = document.getElementById("modele_vehicule");
      updateRepairPrice(select);
    });
  });

document
  .getElementById("modele_vehicule")
  .addEventListener("change", function () {
    updateRepairPrice(this);
  });

$(document).ready(function () {
  $("#modele_vehicule").select2({
    placeholder: "-- Sélectionnez un modèle --",
    allowClear: true,
  });
});
