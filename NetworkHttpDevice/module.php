<?php

declare(strict_types=1);
class HttpDevice extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterVariableProfiles();

        $this->RegisterPropertyString('Url', 'https://google.de');
        $this->RegisterPropertyInteger('Timeout', 1);
        $this->RegisterPropertyInteger('UpdateInterval', 60);

        $this->RegisterPropertyBoolean('VerifyHost', true);
        $this->RegisterPropertyBoolean('VerifyPeer', true);

        $this->RegisterVariableBoolean('Online', $this->Translate('Online'), 'NET_Online', 1);
        $this->RegisterVariableString('Content', $this->Translate('Content'), '', 2);
        $this->RegisterVariableInteger('Statuscode', $this->Translate('Statuscode'), 'NET_Statuscode', 3);

        $this->RegisterTimer('Update', $this->ReadPropertyInteger('UpdateInterval') * 1000, 'NET_Update($_IPS[\'TARGET\']);');



    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->SetTimerInterval('Update', $this->ReadPropertyInteger('UpdateInterval') * 1000);
    }

    public function EnableLogging()
    {
        $archiveId = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];

        $arr = ['Online','Content','Statuscode'];

        foreach ($arr as &$ident) {
            $id = @$this->GetIDForIdent($ident);

            if ($id == 0) {
                continue;
            }
            AC_SetLoggingStatus($archiveId, $id, true);
            AC_SetAggregationType($archiveId, $id, 0); // 0 Standard, 1 Zähler
            AC_SetGraphStatus($archiveId, $id, true);
        }

        IPS_ApplyChanges($archiveId);
    }

    public function DisableLogging()
    {
        $archiveId = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        $arr = ['Online','Content','Statuscode'];

        foreach ($arr as &$ident) {
            $id = $this->GetIDForIdent($ident);
            if ($id == 0) {
                continue;
            }
            AC_SetGraphStatus($archiveId, $id, false);
            AC_SetLoggingStatus($archiveId, $id, false);
        }

        IPS_ApplyChanges($archiveId);
    }

    public function Update()
    {
        $url = $this->ReadPropertyString('Url');
        $timeout = $this->ReadPropertyInteger('Timeout');
        $response = @Sys_Ping($host, $timeout);

        $this->Maintain();


        if ($this->ReadPropertyBoolean('VerifyHost')) {
            $verifyhost = 2;
        } else {
            $verifyhost = 0;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
                CURLOPT_URL => $url ,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSL_VERIFYHOST => $verifyhost,
                CURLOPT_SSL_VERIFYPEER => $this->ReadPropertyBoolean('VerifyPeer'),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $statuscode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $this->SendDebug('Login()', 'Response:' . $response, 0);

        curl_close($curl);

        if($response == false) {
            $response = "";
        }

        $this->SetValue('Online', $err == false);
        $this->SetValue('Content', $response);
        $this->SetValue('Statuscode', $statuscode);



    }
    private function Maintain()
    {
        $this->MaintainVariable('Online', $this->Translate('Online'), 0, 'NET_Online', 1, true);
        $this->MaintainVariable('Content', $this->Translate('Content'), 3, '', 2, true);
        $this->MaintainVariable('Statuscode', $this->Translate('Statuscode'), 1, 'NET_Statuscode', 3, true);
    }


    public function GetState()
    {
        return $this->GetValue('Online');
    }

    public function GetVariables()
    {
        $children = IPS_GetChildrenIDs($this->InstanceID);
        $data = [];

        foreach ($children as &$child) {
            $variable = (IPS_GetObject($child));
            if($variable['ObjectType'] != 2) {
                continue;
            }
            if($variable['ObjectIdent'] != "") {
                $name = $variable['ObjectIdent'];
            } else {
                $name = $variable['ObjectName'];
            }

            $data[$name] = (GetValue($child));

        }

        $data['Timestamp'] = $this->ReadAttributeInteger('LastMessageTimestamp');

        return $data;
    }


    private function RegisterVariableProfiles()
    {
        $this->SendDebug('RegisterVariableProfiles()', 'RegisterVariableProfiles()', 0);

        if (!IPS_VariableProfileExists('NET_Online')) {
            IPS_CreateVariableProfile('NET_Online', 0);
            IPS_SetVariableProfileAssociation('NET_Online', 0, $this->Translate('Offline'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Online', 1, $this->Translate('Online'), 'Ok', 0x00FF00);
        }


        if (!IPS_VariableProfileExists('NET_Statuscode')) {
            IPS_CreateVariableProfile('NET_Statuscode', 1);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 0, $this->Translate('Offline'), 'Warning', 0xFF0000);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 1, "%d", 'Information', 0x000000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 199, "%d", 'Information', 0x000000);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 200, $this->Translate('%d - OK'), 'Ok', 0x00FF00);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 201, "%d", 'Ok', 0x00FF00);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 299, "%d", 'Ok', 0x00FF00);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 300, "%d", 'Warning', 0x000000);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 301, $this->Translate('%d - Moved Permanently'), 'Warning', 0xFF0000);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 302, "%d", 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 399, "%d", 'Warning', 0xFF0000);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 400, $this->Translate('%d - Bad Request'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 401, $this->Translate('%d - Unauthorized'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 402, $this->Translate('%d - Payment Required'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 403, $this->Translate('%d - Forbidden'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 404, $this->Translate('%d - Not Found'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 405, $this->Translate('%d - Method Not Allowed'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 406, $this->Translate('%d - Not Acceptable'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 407, $this->Translate('%d - Proxy Authentication Required'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 408, $this->Translate('%d - Request Timeout'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 409, $this->Translate('%d - Conflict'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 410, "%d", 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 417, "%d", 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 418, $this->Translate('%d - I’m a teapot'), 'Warning', 0xFF0000);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 419, "%d", 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 499, "%d", 'Warning', 0xFF0000);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 500, $this->Translate('%d - Internal Server Error'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 501, $this->Translate('%d - Not Implemented'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 502, $this->Translate('%d - Bad Gateway'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 503, $this->Translate('%d - Service Unavailable'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 504, $this->Translate('%d - Gateway Timeout'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileAssociation('NET_Statuscode', 505, $this->Translate('%d - HTTP Version not supported'), 'Warning', 0xFF0000);

            IPS_SetVariableProfileAssociation('NET_Statuscode', 506, "%d", 'Warning', 0xFF0000);
        }

    }
}
