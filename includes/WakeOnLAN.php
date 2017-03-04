<?php
class WakeOnLAN
{
    public static function wakeUp($macAddressHexadecimal, $broadcastAddress)
    {
        $macAddressHexadecimal = str_replace('-', ':', $macAddressHexadecimal);        
        $macAddressHexadecimal = str_replace(':', '', $macAddressHexadecimal);
        // check if $macAddress is a valid mac address
        if (!ctype_xdigit($macAddressHexadecimal)) {
            throw new \Exception('Mac address invalid, only 0-9 and a-f are allowed');
        }
        $macAddressBinary = pack('H12', $macAddressHexadecimal);
        $magicPacket = str_repeat(chr(0xff), 6).str_repeat($macAddressBinary, 16);

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($sock, 1, 6, true);
        socket_sendto($sock, $magicPacket, strlen($magicPacket), 0, $broadcastAddress, 7);
    }
}
?>
