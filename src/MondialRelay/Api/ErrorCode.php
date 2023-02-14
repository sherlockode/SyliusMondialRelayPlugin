<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api;

class ErrorCode
{
    private const INCORRECT_MERCHANT = 1;
    private const MERCHANT_NUMBER_EMPTY = 2;
    private const INCORRECT_MERCHANT_ACCOUNT_NUMBER = 3;
    private const INCORRECT_MERCHANT_SHIPMENT_REFERENCE = 5;
    private const INCORRECT_CONSIGNEE_REFERENCE = 7;
    private const INCORRECT_PASSWORD_OR_HASH = 8;
    private const UNKNOWN_OR_NOT_UNIQUE_CITY = 9;
    private const INCORRECT_TYPE_OF_COLLECTION = 10;
    private const PICKUP_POINT_COLLECTION_NUMBER_INCORRECT = 11;
    private const PICKUP_POINT_COLLECTION_COUNTRY_INCORRECT = 12;
    private const INCORRECT_TYPE_OF_DELIVERY = 13;
    private const INCORRECT_DELIVERY_PICKUP_POINT_NUMBER = 14;
    private const INCORRECT_DELIVERY_PICKUP_POINT_COUNTRY = 15;
    private const INCORRECT_PARCEL_WEIGHT = 20;
    private const INCORRECT_DEVELOPED_LENGTH = 21;
    private const INCORRECT_PARCEL_SIZE = 22;
    private const INCORRECT_SHIPMENT_NUMBER = 24;
    private const INCORRECT_ASSEMBLY_TIME = 26;
    private const INCORRECT_MODE_OF_COLLECTION_OR_DELIVERY = 27;
    private const INCORRECT_MODE_OF_COLLECTION = 28;
    private const INCORRECT_MODE_OF_DELIVERY = 29;
    private const INCORRECT_ADDRESS_L1 = 30;
    private const INCORRECT_ADDRESS_L2 = 31;
    private const INCORRECT_ADDRESS_L3 = 33;
    private const INCORRECT_ADDRESS_L4 = 34;
    private const INCORRECT_CITY = 35;
    private const INCORRECT_ZIPCODE = 36;
    private const INCORRECT_COUNTRY = 37;
    private const INCORRECT_PHONE_NUMBER = 38;
    private const INCORRECT_EMAIL = 39;
    private const MISSING_PARAMETERS = 40;
    private const INCORRECT_CODE_VALUE = 42;
    private const INCORRECT_CODE_CURRENCY = 43;
    private const INCORRECT_SHIPMENT_VALUE = 44;
    private const INCORRECT_SHIPMENT_VALUE_CURRENCY = 45;
    private const END_OF_SHIPMENTS_NUMBER_RANGE_REACHED = 46;
    private const INCORRECT_NUMBER_OF_PARCELS = 47;
    private const MULTI_PARCEL_NOT_PERMITTED = 48;
    private const INCORRECT_ACTION = 49;
    private const INCORRECT_TEXT_FIELD = 60;
    private const INCORRECT_NOTIFICATION_REQUEST = 61;
    private const INCORRECT_EXTRA_DELIVERY_INFORMATION = 62;
    private const INCORRECT_INSURANCE = 63;
    private const INCORRECT_ASSEMBLY_TIME_BIS = 64;
    private const INCORRECT_APPOINTMENT = 65;
    private const INCORRECT_TAKE_BACK = 66;
    private const INCORRECT_LATITUDE = 67;
    private const INCORRECT_LONGITUDE = 68;
    private const INCORRECT_MERCHANT_CODE = 69;
    private const INCORRECT_PICKUP_POINT_NUMBER = 70;
    private const INCORRECT_PICKUP_POINT_TYPE = 71;
    private const INCORRECT_LANGUAGE = 74;
    private const INCORRECT_COLLECTION_COUNTRY = 78;
    private const INCORRECT_DELIVERY_COUNTRY = 79;
    private const INCORRECT_TRACKING_CODE_RECORDED_PARCEL = 80;
    private const INCORRECT_TRACKING_CODE_PROCESSING_PARCEL = 81;
    private const INCORRECT_TRACKING_CODE_DELIVERED_PARCEL = 82;
    private const INCORRECT_TRACKING_CODE_ANOMALY = 83;
    private const UNKNOWN_PARCEL = 94;
    private const MERCHANT_ACCOUNT_NOT_ENABLE = 95;
    private const INCORRECT_STORE_TYPE = 96;
    private const INCORRECT_SECURITY_KEY = 97;
    private const GENERIC_ERROR = 98;
    private const SYSTEM_GENERIC_ERROR = 99;

    private const ERRORS = [
        self::INCORRECT_MERCHANT => 'invalid_merchant',
        self::MERCHANT_NUMBER_EMPTY => 'empty_merchant_number',
        self::INCORRECT_MERCHANT_ACCOUNT_NUMBER => 'invalid_merchant_account_number',
        self::INCORRECT_MERCHANT_SHIPMENT_REFERENCE => 'invalid_merchant_shipment_reference',
        self::INCORRECT_CONSIGNEE_REFERENCE => 'invalid_consignee_reference',
        self::INCORRECT_PASSWORD_OR_HASH => 'invalid_password_or_hash',
        self::UNKNOWN_OR_NOT_UNIQUE_CITY => 'unknown_or_not_unique_city',
        self::INCORRECT_TYPE_OF_COLLECTION => 'invalid_collection_type',
        self::PICKUP_POINT_COLLECTION_NUMBER_INCORRECT => 'invalid_collection_pickup_point_number',
        self::PICKUP_POINT_COLLECTION_COUNTRY_INCORRECT => 'invalid_collection_pickup_point_country',
        self::INCORRECT_TYPE_OF_DELIVERY => 'invalid_delivery_type',
        self::INCORRECT_DELIVERY_PICKUP_POINT_NUMBER => 'invalid_delivery_pickup_point_number',
        self::INCORRECT_DELIVERY_PICKUP_POINT_COUNTRY => 'invalid_delivery_pickup_point_country',
        self::INCORRECT_PARCEL_WEIGHT => 'invalid_parcel_weight',
        self::INCORRECT_DEVELOPED_LENGTH => 'invalid_developed_length',
        self::INCORRECT_PARCEL_SIZE => 'invalid_parcel_size',
        self::INCORRECT_SHIPMENT_NUMBER => 'invalid_shipment_number',
        self::INCORRECT_ASSEMBLY_TIME => 'invalid_assembly_time',
        self::INCORRECT_MODE_OF_COLLECTION_OR_DELIVERY => 'invalid_collection_or_delivery_mode',
        self::INCORRECT_MODE_OF_COLLECTION => 'invalid_collection_mode',
        self::INCORRECT_MODE_OF_DELIVERY => 'invalid_delivery_mode',
        self::INCORRECT_ADDRESS_L1 => 'invalid_address_l1',
        self::INCORRECT_ADDRESS_L2 => 'invalid_address_l2',
        self::INCORRECT_ADDRESS_L3 => 'invalid_address_l3',
        self::INCORRECT_ADDRESS_L4 => 'invalid_address_l4',
        self::INCORRECT_CITY => 'invalid_city',
        self::INCORRECT_ZIPCODE => 'invalid_zipcode',
        self::INCORRECT_COUNTRY => 'invalid_country',
        self::INCORRECT_PHONE_NUMBER => 'invalid_phone_number',
        self::INCORRECT_EMAIL => 'invalid_email',
        self::MISSING_PARAMETERS => 'missing_parameters',
        self::INCORRECT_CODE_VALUE => 'invalid_code_value',
        self::INCORRECT_CODE_CURRENCY => 'invalid_code_currency',
        self::INCORRECT_SHIPMENT_VALUE => 'invalid_shipment_value',
        self::INCORRECT_SHIPMENT_VALUE_CURRENCY => 'invalid_shipment_value_currency',
        self::END_OF_SHIPMENTS_NUMBER_RANGE_REACHED => 'end_of_shipments_number_range_reached',
        self::INCORRECT_NUMBER_OF_PARCELS => 'invalid_number_of_parcels',
        self::MULTI_PARCEL_NOT_PERMITTED => 'multi_parcel_not_allowed',
        self::INCORRECT_ACTION => 'invalid_action',
        self::INCORRECT_TEXT_FIELD => 'invalid_text_field',
        self::INCORRECT_NOTIFICATION_REQUEST => 'invalid_notification_request',
        self::INCORRECT_EXTRA_DELIVERY_INFORMATION => 'invalid_extra_delivery_information',
        self::INCORRECT_INSURANCE => 'invalid_insurance',
        self::INCORRECT_ASSEMBLY_TIME_BIS => 'invalid_assembly_time',
        self::INCORRECT_APPOINTMENT => 'invalid_appointment',
        self::INCORRECT_TAKE_BACK => 'invalid_take_back',
        self::INCORRECT_LATITUDE => 'invalid_latitude',
        self::INCORRECT_LONGITUDE => 'invalid_longitude',
        self::INCORRECT_MERCHANT_CODE => 'invalid_merchant_code',
        self::INCORRECT_PICKUP_POINT_NUMBER => 'invalid_pickup_point_number',
        self::INCORRECT_PICKUP_POINT_TYPE => 'invalid_pickup_point_type',
        self::INCORRECT_LANGUAGE => 'invalid_language',
        self::INCORRECT_COLLECTION_COUNTRY => 'invalid_collection_country',
        self::INCORRECT_DELIVERY_COUNTRY => 'invalid_delivery_country',
        self::INCORRECT_TRACKING_CODE_RECORDED_PARCEL => 'invalid_tracking_code_recorded_parcel',
        self::INCORRECT_TRACKING_CODE_PROCESSING_PARCEL => 'invalid_tracking_code_processing_parcel',
        self::INCORRECT_TRACKING_CODE_DELIVERED_PARCEL => 'invalid_tracking_code_delivered_parcel',
        self::INCORRECT_TRACKING_CODE_ANOMALY => 'invalid_tracking_code_anomaly',
        self::UNKNOWN_PARCEL => 'unknown_parcel',
        self::MERCHANT_ACCOUNT_NOT_ENABLE => 'merchant_account_not_enabled',
        self::INCORRECT_STORE_TYPE => 'invalid_store_type',
        self::INCORRECT_SECURITY_KEY => 'invalid_security_key',
        self::GENERIC_ERROR => 'generic_error',
        self::SYSTEM_GENERIC_ERROR => 'system_generic_error',
    ];

    /**
     * @param int $errorCode
     *
     * @return string
     */
    public static function getErrorMessageKey(int $errorCode): string
    {
        return self::ERRORS[$errorCode] ?? 'default';
    }
}
