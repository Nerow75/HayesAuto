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
function updateContractPrice() {
  // Récupère le tarif de base sélectionné
  const selectTarif = document.getElementById("tarif");
  let basePrice = 0;
  if (selectTarif && selectTarif.value) {
    basePrice = parseFloat(selectTarif.value) || 0;
  }

  // Additionne le prix des révisions cochées
  let revisionTotal = 0;
  document
    .querySelectorAll('input[name="revision_items[]"]:checked')
    .forEach(function (checkbox) {
      revisionTotal += parseFloat(checkbox.getAttribute("data-price")) || 0;
    });

  // Affiche le total dans le champ readonly
  const totalField = document.getElementById("tarif_total");
  if (totalField) {
    totalField.value = basePrice + revisionTotal;
  }
}

// Pour le type contrat, ajoute les écouteurs
if (
  document.getElementById("tarif") &&
  document.getElementById("tarif_total")
) {
  document
    .getElementById("tarif")
    .addEventListener("change", updateContractPrice);
  document
    .querySelectorAll('input[name="revision_items[]"]')
    .forEach(function (checkbox) {
      checkbox.addEventListener("change", updateContractPrice);
    });
  // Initialisation
  updateContractPrice();
}
function updateTarif() {
  let basePrice = 0;

  // Pour contrat, récupère la valeur du select
  const selectTarifBase = document.getElementById("tarif_base");
  if (selectTarifBase) {
    basePrice = parseFloat(selectTarifBase.value) || 0;
  } else {
    // Pour vente classique, récupère le prix du modèle si non "uniquement révision"
    const onlyRevision = document.getElementById("only_revision")?.checked;
    const selectModele = document.getElementById("modele_vehicule");
    if (!onlyRevision && selectModele && selectModele.selectedIndex > 0) {
      const selectedOption = selectModele.options[selectModele.selectedIndex];
      basePrice =
        parseFloat(selectedOption.getAttribute("data-price-sell")) || 0;
    }
  }

  // Ajoute le prix des révisions cochées
  let revisionTotal = 0;
  document
    .querySelectorAll('input[name="revision_items[]"]:checked')
    .forEach(function (checkbox) {
      revisionTotal += parseFloat(checkbox.getAttribute("data-price")) || 0;
    });

  // Affiche le total dans le champ readonly
  const tarifField = document.getElementById("tarif");
  if (tarifField) {
    tarifField.value = basePrice + revisionTotal;
  }
}

// Écouteurs pour tous les cas
document
  .getElementById("only_revision")
  ?.addEventListener("change", updateTarif);
document
  .getElementById("modele_vehicule")
  ?.addEventListener("change", updateTarif);
document.getElementById("tarif_base")?.addEventListener("change", updateTarif);
document
  .querySelectorAll('input[name="revision_items[]"]')
  .forEach(function (checkbox) {
    checkbox.addEventListener("change", updateTarif);
  });

// Initialisation au chargement
updateTarif();
