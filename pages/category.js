function modifyCategory(category) {
    document.getElementById("modositas_form").style.display = "block";
    document.getElementById("eredeti_nev").value = category;
    document.getElementById("modositas_nev").value = category;
}

function closeModificationForm() {
    document.getElementById("modositas_form").style.display = "none";
}