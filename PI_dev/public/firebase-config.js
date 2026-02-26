/**
 * Firebase Configuration
 * 
 * IMPORTANT: Remplacez ces valeurs par vos propres clés Firebase
 * Obtenez-les depuis: https://console.firebase.google.com/
 * Project Settings > General > Your apps > Web app
 */

const firebaseConfig = {
    apiKey: "YOUR_API_KEY_HERE",
    authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_PROJECT_ID.appspot.com",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID",
    measurementId: "YOUR_MEASUREMENT_ID" // Optionnel
};

// VAPID Key pour Web Push
// Obtenez-la depuis: Project Settings > Cloud Messaging > Web Push certificates
const vapidKey = "YOUR_VAPID_KEY_HERE";

// Export configuration
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { firebaseConfig, vapidKey };
}
