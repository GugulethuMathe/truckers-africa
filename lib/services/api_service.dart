import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/driver.dart';

class ApiService {
  static const String baseUrl = 'http://172.191.103.110/api/v1';
  static const String _tokenKey = 'jwt_token';
  
  late final Dio _dio;
  static const FlutterSecureStorage _storage = FlutterSecureStorage();

  ApiService() {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    // Add interceptor for authentication
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await getToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) {
        // Handle token expiration
        if (error.response?.statusCode == 401) {
          clearToken();
        }
        handler.next(error);
      },
    ));
  }

  // Token management
  Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }

  Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  Future<void> clearToken() async {
    await _storage.delete(key: _tokenKey);
  }

  Future<bool> isAuthenticated() async {
    final token = await getToken();
    return token != null;
  }

  // Authentication endpoints
  Future<LoginResponse> login(LoginRequest request) async {
    try {
      final response = await _dio.post('/drivers/login', data: request.toJson());
      final loginResponse = LoginResponse.fromJson(response.data);
      await saveToken(loginResponse.token);
      return loginResponse;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<LoginResponse> register(RegisterRequest request) async {
    try {
      final response = await _dio.post('/drivers/register', data: request.toJson());
      final loginResponse = LoginResponse.fromJson(response.data);
      await saveToken(loginResponse.token);
      return loginResponse;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Driver> getProfile() async {
    try {
      final response = await _dio.get('/driver/profile');
      return Driver.fromJson(response.data);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<Driver> updateProfile(Map<String, dynamic> data) async {
    try {
      final response = await _dio.post('/driver/profile', data: data);
      return Driver.fromJson(response.data);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<void> updateLocation(double latitude, double longitude) async {
    try {
      await _dio.post('/driver/location', data: {
        'latitude': latitude,
        'longitude': longitude,
      });
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<void> logout() async {
    await clearToken();
  }

  // Error handling
  String _handleError(DioException error) {
    if (error.response != null) {
      final data = error.response!.data;
      if (data is Map<String, dynamic> && data.containsKey('message')) {
        return data['message'];
      }
      return 'Server error: ${error.response!.statusCode}';
    } else if (error.type == DioExceptionType.connectionTimeout ||
               error.type == DioExceptionType.receiveTimeout) {
      return 'Connection timeout. Please check your internet connection.';
    } else if (error.type == DioExceptionType.connectionError) {
      return 'No internet connection. Please check your network.';
    }
    return 'An unexpected error occurred.';
  }
}
