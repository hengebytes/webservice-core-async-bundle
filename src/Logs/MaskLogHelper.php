<?php

namespace WebserviceCoreAsyncBundle\Logs;

abstract class MaskLogHelper
{
    public static function maskSensitiveVar(&$val, bool $maskMemberPII): void
    {
        // 'assword' cover 'Password' & 'password'
        $patterns = [
            '#assword>[^<]*</#',
            '#assword&gt;(.*?)&lt;/#',
            '#assword="[^"]*"#',
            '#assword":\s?"[^"]*"#',
            '#assword";s:(\d+)\:"[^"]*?"#',
            '#ardNumber":\s?"[^"]*"#',
            '#ardNumber>(\d+)([\d]{4})<#',
            '#ardNumber&gt;(\d+)([\d]{4})&lt;#',
            '#"cardNumber";s:(\d+)\:"(\d+)([\d]{4})"#',
            '#"number":\s?"\d+"#',
            '#ardNumber="(\d+)([\d]{4})"#',
            '#token=\s?"[^"]*"#',
            '#token":\s?"[^"]*"#',
        ];
        if ($maskMemberPII) {
            $patterns = array_merge($patterns, [
                // ADDRESS[1-9]? - covers <EMAIL_ADDRESS>
                '#irthDate="[^"]*"#',
                '#"[^@"]+@[^@"]+"#',
                '#>[^@<]+@[^@<]+<#',
                '#(Birthdate|AddressLine|postalCode|PostalCode|CityName|cityName|CITY|ZIP_CODE|NameEmail|BIRTH_DATE|ADDRESS[1-9]?)(.*?)(>|&gt;).*?(<|&lt;)/#',
                '#(city|zipCode|zip|birthday|birthdate|address[1-9]?)":\s?"[^"]*"#',
            ]);
        }
        $replacements = [
            'assword>*</',
            'assword&gt;*&lt;/',
            'assword="*"',
            'assword":"*"',
            'assword";s:1:"*"',
            'ardNumber":"*"',
            'ardNumber>*${2}<',
            'ardNumber&gt;*${2}&lt;',
            '"cardNumber";s:5:"*${3}"',
            '"number":"*"',
            'ardNumber="*${2}"',
            'token="*"',
            'token:"*"',
        ];
        if ($maskMemberPII) {
            $replacements = array_merge($replacements, [
                'irthDate="*"',
                '"*@*"',
                '>*@*<',
                '${1}${2}${3}*${4}/',
                '${1}":"*"',
            ]);
        }

        if (is_string($val)) {
            $val = preg_replace($patterns, $replacements, $val);

            return;
        }
        if (is_array($val)) {
            foreach ($val as $innerK => $innerVal) {
                if ($innerK === 'creditCard' && isset($innerVal['number'])) {
                    $val[$innerK]['number'] = '*';
                } elseif (
                    stripos($innerK, 'password') !== false
                    || stripos($innerK, 'cardnumber') !== false
                    || (
                        $maskMemberPII && (
                            stripos($innerK, 'address') !== false
                            || stripos($innerK, 'birth') !== false
                            || stripos($innerK, 'email') !== false
                        )
                    )
                ) {
                    $val[$innerK] = '*';
                } elseif (is_string($innerVal)) {
                    $val[$innerK] = preg_replace($patterns, $replacements, $innerVal);
                } elseif (is_array($innerVal)) {
                    self::maskSensitiveVar($val[$innerK], $maskMemberPII);
                }
            }
        }
    }
}