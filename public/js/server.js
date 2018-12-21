function Server() {

	// Ключ пользователя
	let token,
		map_id;

	// Обращения к серверу
	this.changeDirection = function (direction = 'left') {
		return $.get('api', { method: 'changeDirection', map_id, direction, token });
	};
    this.getMaps = function () {
        return $.get('api', { method: 'getMaps', token });
    };
    this.startGame = function (map_id = 0) {
        return $.get('api', { method: 'startGame', map_id, token });
    };

    this.login = async function (options = {}) {
        const result = await $.get('api', { method: 'login', ...options });
        if(result.result) {
            token = result.data.token;
        }
        return result;
    };
	this.register = function (options) {
		return $.get('api', { method: 'register', ...options });
	};
	this.logout = function () {
		return $.get('api', { method: 'logout'});
	};
    this.getScene = function() {
        return $.get('api', { method: 'getScene', map_id, token });
    };
    this.getLeaderBoard = function() {
        return $.get('api', { method: 'getLeaderBoard', token });
    };

    // Токены
	this.getToken = () => {
    	return token;
	};
	// Карта
    this.setMapId = (mapId = '') => {
        map_id = mapId;
    };

}