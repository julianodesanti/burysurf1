/**
 * BurySurfDB - JavaScript Database Helper
 * Connects to PHP API endpoints to manage surf conditions
 * 
 * Usage:
 * const db = new BurySurfDB('/api/');
 * const conditions = await db.getAllConditions();
 */

class BurySurfDB {
    /**
     * @param {string} apiBaseUrl - Base URL for API endpoints (default: '/api/')
     */
    constructor(apiBaseUrl = '/api/') {
        this.apiBaseUrl = apiBaseUrl;
    }

    /**
     * Fetch all current conditions for all spots
     * @returns {Promise<Array>} Array of condition objects
     */
    async getAllConditions() {
        try {
            const response = await fetch(`${this.apiBaseUrl}get_conditions.php`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('Error in getAllConditions:', error);
            return [];
        }
    }

    /**
     * Fetch conditions for a specific spot
     * @param {number} spotId - Spot ID
     * @returns {Promise<Object>} Condition object
     */
    async getSpotConditions(spotId) {
        try {
            const response = await fetch(`${this.apiBaseUrl}get_spot_conditions.php?spot_id=${spotId}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('Error in getSpotConditions:', error);
            return null;
        }
    }

    /**
     * Update conditions for a spot
     * @param {number} spotId - Spot ID
     * @param {string} waveSize - Wave size
     * @param {string} waveFormation - Wave formation
     * @param {string} weather - Weather condition
     * @param {string} wind - Wind condition
     * @param {string} waterTemp - Water temperature
     * @returns {Promise<Object>} Response object
     */
    async updateConditions(spotId, waveSize, waveFormation, weather, wind, waterTemp) {
        try {
            const today = new Date().toISOString().split('T')[0];
            
            const response = await fetch(`${this.apiBaseUrl}update_conditions.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    spot_id: spotId,
                    condition_date: today,
                    wave_size: waveSize,
                    wave_formation: waveFormation,
                    weather: weather,
                    wind: wind,
                    water_temperature: waterTemp
                })
            });
            
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('Error in updateConditions:', error);
            return { success: false };
        }
    }

    /**
     * Get historical data for a spot
     * @param {number} spotId - Spot ID
     * @param {number} days - Number of days (default: 30)
     * @returns {Promise<Array>} Array of historical condition objects
     */
    async getHistoricalData(spotId, days = 30) {
        try {
            const response = await fetch(`${this.apiBaseUrl}get_historical_data.php?spot_id=${spotId}&days=${days}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('Error in getHistoricalData:', error);
            return [];
        }
    }
}
