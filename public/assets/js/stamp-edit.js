/**
 * Stamp Edit Page JavaScript
 * Handles image management and form validation for stamp editing
 */

document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.getElementById("stamp_images");

    // Handle new image uploads
    imageInput.addEventListener("change", function () {
        const files = Array.from(this.files);
        const maxFiles = 5;
        const maxSize = 1 * 1024 * 1024; // 1MB

        if (files.length > maxFiles) {
            alert(`Vous ne pouvez sélectionner que ${maxFiles} images maximum.`);
            this.value = "";
            return;
        }

        for (let file of files) {
            if (file.size > maxSize) {
                alert(`L'image "${file.name}" est trop volumineuse (max 1MB).`);
                this.value = "";
                return;
            }
        }
    });
});

function setAsMain(imageId) {
    if (confirm("Définir cette image comme image principale ?")) {
        const formData = new FormData();
        formData.append("image_id", imageId);
        formData.append("_token", csrfToken);

        fetch(baseUrl + "/stamps/image/set-main", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert("Image principale mise à jour avec succès !");
                    location.reload(); // Refresh to show changes
                } else {
                    alert("Erreur: " + (data.message || "Impossible de définir comme image principale"));
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Erreur de communication avec le serveur");
            });
    }
}

function deleteImage(imageId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cette image ?")) {
        const formData = new FormData();
        formData.append("image_id", imageId);
        formData.append("_token", csrfToken);

        fetch(baseUrl + "/stamps/image/delete", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert("Image supprimée avec succès !");
                    location.reload(); // Refresh to show changes
                } else {
                    alert("Erreur: " + (data.message || "Impossible de supprimer l'image"));
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Erreur de communication avec le serveur");
            });
    }
}
