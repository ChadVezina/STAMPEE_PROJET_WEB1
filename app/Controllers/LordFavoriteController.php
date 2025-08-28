<?php
// DEAD CODE - This file is no longer used
// It was replaced by FavoriteController for auction-based favorites
// This file can be safely deleted
die('This controller has been deprecated. Use FavoriteController instead.');
?>

/**
* Show login form for Lord access
*/
public function showLogin()
{
// Check if already authenticated
if ($this->isLordAuthenticated()) {
header('Location: ' . Config::get('app.base_url') . '/lord/favorites/manage');
exit;
}

View::render('pages/lord/login', [
'title' => 'Accès Lord - Coups de Cœur',
'error' => $_SESSION['lord_error'] ?? null
]);

// Clear error message
unset($_SESSION['lord_error']);
}

/**
* Handle login authentication
*/
public function login()
{
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: ' . Config::get('app.base_url') . '/lord/login');
exit;
}

$password = $_POST['password'] ?? '';

if ($password === $this->lordPassword) {
$_SESSION['lord_authenticated'] = true;
header('Location: ' . Config::get('app.base_url') . '/lord/favorites/manage');
exit;
} else {
$_SESSION['lord_error'] = 'Mot de passe incorrect';
header('Location: ' . Config::get('app.base_url') . '/lord/login');
exit;
}
}

/**
* Show management interface for Lord favorites
*/
public function manage()
{
if (!$this->isLordAuthenticated()) {
header('Location: ' . Config::get('app.base_url') . '/lord/login');
exit;
}

try {
// Get all stamps with details
$allStamps = $this->getAllStampsWithDetails();

// Get current favorites
$currentFavorites = $this->getAllFavoriteStamps();
$favoriteStampIds = array_column($currentFavorites, 'stamp_id');

View::render('pages/lord/manage-favorites', [
'title' => 'Gestion des Coups de Cœur du Lord',
'stamps' => $allStamps,
'favoriteStampIds' => $favoriteStampIds,
'success' => $_SESSION['success_message'] ?? null,
'error' => $_SESSION['error_message'] ?? null
]);

// Clear messages
unset($_SESSION['success_message'], $_SESSION['error_message']);

} catch (\Exception $e) {
View::render('pages/lord/manage-favorites', [
'title' => 'Gestion des Coups de Cœur du Lord',
'error' => 'Erreur lors du chargement: ' . $e->getMessage(),
'stamps' => [],
'favoriteStampIds' => []
]);
}
}

/**
* Toggle favorite status for a stamp
*/
public function toggleFavorite()
{
if (!$this->isLordAuthenticated()) {
header('Location: ' . Config::get('app.base_url') . '/lord/login');
exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: ' . Config::get('app.base_url') . '/lord/favorites/manage');
exit;
}

$stampId = (int)($_POST['stamp_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($stampId > 0) {
try {
if ($action === 'add') {
if ($this->addStampToFavorites($stampId)) {
$_SESSION['success_message'] = 'Timbre ajouté aux Coups de Cœur du Lord';
} else {
$_SESSION['error_message'] = 'Erreur lors de l\'ajout du timbre';
}
} elseif ($action === 'remove') {
if ($this->removeStampFromFavorites($stampId)) {
$_SESSION['success_message'] = 'Timbre retiré des Coups de Cœur du Lord';
} else {
$_SESSION['error_message'] = 'Erreur lors de la suppression du timbre';
}
}
} catch (\Exception $e) {
$_SESSION['error_message'] = 'Erreur: ' . $e->getMessage();
}
}

header('Location: ' . Config::get('app.base_url') . '/lord/favorites/manage');
exit;
}

/**
* Logout from Lord interface
*/
public function logout()
{
unset($_SESSION['lord_authenticated']);
header('Location: ' . Config::get('app.base_url') . '/');
exit;
}

/**
* API endpoint to get current favorites (for home page)
*/
public function getFavoritesApi()
{
header('Content-Type: application/json');

try {
$favorites = $this->getAllFavoriteStampsWithDetails();
echo json_encode([
'success' => true,
'favorites' => $favorites
]);
} catch (\Exception $e) {
echo json_encode([
'success' => false,
'error' => $e->getMessage()
]);
}
}

/**
* Check if Lord is authenticated
*/
private function isLordAuthenticated(): bool
{
return isset($_SESSION['lord_authenticated']) && $_SESSION['lord_authenticated'] === true;
}

/**
* Get all stamps with details for management interface
*/
private function getAllStampsWithDetails(): array
{
$sql = "
SELECT s.id, s.name, s.country_code, s.user_id,
c.name_fr AS country_name,
u.nom as owner_name,
si.url AS main_image
FROM `Stamp` s
LEFT JOIN `Country` c ON c.iso2 = s.country_code
LEFT JOIN `User` u ON u.id = s.user_id
LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
ORDER BY s.name ASC
";

$stmt = DB::pdo()->query($sql);
return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

/**
* Get all favorite stamps (just stamp IDs)
*/
private function getAllFavoriteStamps(): array
{
$stmt = DB::pdo()->query("SELECT stamp_id FROM `Favorite` ORDER BY created_at DESC");
return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

/**
* Get all favorite stamps with full details
*/
private function getAllFavoriteStampsWithDetails(): array
{
$sql = "
SELECT f.id as favorite_id, f.stamp_id, f.created_at as favorite_date,
s.name, s.country_code, s.user_id,
c.name_fr AS country_name,
u.nom as owner_name,
si.url AS main_image
FROM `Favorite` f
INNER JOIN `Stamp` s ON f.stamp_id = s.id
LEFT JOIN `Country` c ON c.iso2 = s.country_code
LEFT JOIN `User` u ON u.id = s.user_id
LEFT JOIN `StampImage` si ON si.stamp_id = s.id AND si.is_main = 1
ORDER BY f.created_at DESC
";

$stmt = DB::pdo()->query($sql);
return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

/**
* Add a stamp to favorites
*/
private function addStampToFavorites(int $stampId): bool
{
// Check if already favorite
$check = DB::pdo()->prepare("SELECT 1 FROM `Favorite` WHERE stamp_id = ? LIMIT 1");
$check->execute([$stampId]);
if ($check->fetchColumn()) {
return true; // Already favorite
}

$stmt = DB::pdo()->prepare("INSERT INTO `Favorite` (stamp_id, created_at) VALUES (?, NOW())");
return $stmt->execute([$stampId]);
}

/**
* Remove a stamp from favorites
*/
private function removeStampFromFavorites(int $stampId): bool
{
$stmt = DB::pdo()->prepare("DELETE FROM `Favorite` WHERE stamp_id = ?");
return $stmt->execute([$stampId]);
}
}