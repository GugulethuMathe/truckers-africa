import 'package:json_annotation/json_annotation.dart';

part 'driver.g.dart';

@JsonSerializable()
class Driver {
  final int id;
  final String name;
  final String email;
  @JsonKey(name: 'profile_image_url')
  final String? profileImageUrl;
  final String? surname;
  @JsonKey(name: 'contact_number')
  final String? contactNumber;
  @JsonKey(name: 'whatsapp_number')
  final String? whatsappNumber;
  @JsonKey(name: 'country_of_residence')
  final String? countryOfResidence;
  @JsonKey(name: 'vehicle_type')
  final String? vehicleType;

  Driver({
    required this.id,
    required this.name,
    required this.email,
    this.profileImageUrl,
    this.surname,
    this.contactNumber,
    this.whatsappNumber,
    this.countryOfResidence,
    this.vehicleType,
  });

  factory Driver.fromJson(Map<String, dynamic> json) => _$DriverFromJson(json);
  Map<String, dynamic> toJson() => _$DriverToJson(this);

  Driver copyWith({
    int? id,
    String? name,
    String? email,
    String? profileImageUrl,
    String? surname,
    String? contactNumber,
    String? whatsappNumber,
    String? countryOfResidence,
    String? vehicleType,
  }) {
    return Driver(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      profileImageUrl: profileImageUrl ?? this.profileImageUrl,
      surname: surname ?? this.surname,
      contactNumber: contactNumber ?? this.contactNumber,
      whatsappNumber: whatsappNumber ?? this.whatsappNumber,
      countryOfResidence: countryOfResidence ?? this.countryOfResidence,
      vehicleType: vehicleType ?? this.vehicleType,
    );
  }
}

@JsonSerializable()
class LoginResponse {
  final String token;
  final Driver driver;

  LoginResponse({
    required this.token,
    required this.driver,
  });

  factory LoginResponse.fromJson(Map<String, dynamic> json) => _$LoginResponseFromJson(json);
  Map<String, dynamic> toJson() => _$LoginResponseToJson(this);
}

@JsonSerializable()
class LoginRequest {
  final String email;
  final String password;

  LoginRequest({
    required this.email,
    required this.password,
  });

  factory LoginRequest.fromJson(Map<String, dynamic> json) => _$LoginRequestFromJson(json);
  Map<String, dynamic> toJson() => _$LoginRequestToJson(this);
}

@JsonSerializable()
class RegisterRequest {
  final String name;
  final String email;
  final String password;
  @JsonKey(name: 'contact_number')
  final String contactNumber;

  RegisterRequest({
    required this.name,
    required this.email,
    required this.password,
    required this.contactNumber,
  });

  factory RegisterRequest.fromJson(Map<String, dynamic> json) => _$RegisterRequestFromJson(json);
  Map<String, dynamic> toJson() => _$RegisterRequestToJson(this);
}
