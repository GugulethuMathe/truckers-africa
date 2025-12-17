import 'package:flutter/foundation.dart';
import '../models/driver.dart';
import '../services/api_service.dart';

enum AuthState { initial, loading, authenticated, unauthenticated, error }

class AuthProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();
  
  AuthState _state = AuthState.initial;
  Driver? _driver;
  String? _errorMessage;

  AuthState get state => _state;
  Driver? get driver => _driver;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _state == AuthState.authenticated;
  bool get isLoading => _state == AuthState.loading;

  AuthProvider() {
    _checkAuthStatus();
  }

  Future<void> _checkAuthStatus() async {
    try {
      final isAuth = await _apiService.isAuthenticated();
      if (isAuth) {
        _setState(AuthState.loading);
        final driver = await _apiService.getProfile();
        _driver = driver;
        _setState(AuthState.authenticated);
      } else {
        _setState(AuthState.unauthenticated);
      }
    } catch (e) {
      _setState(AuthState.unauthenticated);
    }
  }

  Future<bool> login(String email, String password) async {
    try {
      _setState(AuthState.loading);
      _clearError();

      final request = LoginRequest(email: email, password: password);
      final response = await _apiService.login(request);
      
      _driver = response.driver;
      _setState(AuthState.authenticated);
      return true;
    } catch (e) {
      _setError(e.toString());
      _setState(AuthState.unauthenticated);
      return false;
    }
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String contactNumber,
  }) async {
    try {
      _setState(AuthState.loading);
      _clearError();

      final request = RegisterRequest(
        name: name,
        email: email,
        password: password,
        contactNumber: contactNumber,
      );
      
      final response = await _apiService.register(request);
      
      _driver = response.driver;
      _setState(AuthState.authenticated);
      return true;
    } catch (e) {
      _setError(e.toString());
      _setState(AuthState.unauthenticated);
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.logout();
      _driver = null;
      _setState(AuthState.unauthenticated);
    } catch (e) {
      // Even if logout fails, clear local state
      _driver = null;
      _setState(AuthState.unauthenticated);
    }
  }

  Future<bool> updateProfile(Map<String, dynamic> data) async {
    try {
      _setState(AuthState.loading);
      _clearError();

      final updatedDriver = await _apiService.updateProfile(data);
      _driver = updatedDriver;
      _setState(AuthState.authenticated);
      return true;
    } catch (e) {
      _setError(e.toString());
      _setState(AuthState.authenticated); // Keep authenticated state
      return false;
    }
  }

  void _setState(AuthState newState) {
    _state = newState;
    notifyListeners();
  }

  void _setError(String error) {
    _errorMessage = error;
    notifyListeners();
  }

  void _clearError() {
    _errorMessage = null;
  }

  void clearError() {
    _clearError();
    notifyListeners();
  }
}
