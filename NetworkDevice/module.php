<?php

declare(strict_types=1);
    class NetworkDevice extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyString('Host', '127.0.0.1');
            $this->RegisterPropertyInteger('Timeout', 1000);
            $this->RegisterPropertyInteger('RetryError', 5);
            $this->RegisterPropertyInteger('RetryOk', 5);
            $this->RegisterPropertyInteger('UpdateInterval', 60);
          

            $this->RegisterVariableBoolean('Online', $this->Translate('Online'), '~Alert.Reversed', 1);

            $this->RegisterTimer('Update', $this->ReadPropertyInteger('UpdateInterval') * 1000, 'NET_Update($_IPS[\'TARGET\']);');

            $this->SetBuffer('ErrorCount', 0);
            $this->SetBuffer('OnlineCount', 0);
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
            $this->SetTimerInterval('Update', $this->ReadPropertyInteger('UpdateInterval') * 1000);
/*
            if ($this->ReadPropertyBoolean('Logging')) {
                $archiveId = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];

                AC_SetLoggingStatus($archiveId, $this->GetIDForIdent('Online'), true);
                AC_SetAggregationType($archiveId, $this->GetIDForIdent('Online'), 0); // 0 Standard, 1 Zähler
                AC_SetGraphStatus($archiveId, $this->GetIDForIdent('Online'), true);

                IPS_ApplyChanges($archiveId);
            } else {
                $archiveId = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];

                AC_SetLoggingStatus($archiveId, $this->GetIDForIdent('Online'), false);
                AC_SetGraphStatus($archiveId, $this->GetIDForIdent('Online'), false);

                IPS_ApplyChanges($archiveId);
            }
			*/
        }

        public function Update()
        {
            $host = $this->ReadPropertyString('Host');
            $timeout = $this->ReadPropertyInteger('Timeout');

            $response = Sys_Ping($host, $timeout);

            if ($response) {
                $this->SendDebug('Update()', 'Sys_Ping('.$host.','.$timeout.') => true', 0);
                if ($this->GetBuffer('ErrorCount') != '0') {
                    $this->SetBuffer('ErrorCount', 0);
                    $this->SendDebug('Update()', 'ErrorCount => 0', 0);
                }

                if (intval($this->GetBuffer('OnlineCount')) >= $this->ReadPropertyInteger('RetryOk')) {
                    $this->SetValue('Online', true);
                } else {
                    $this->SetBuffer('OnlineCount', intval($this->GetBuffer('OnlineCount')) + 1);
                    $this->SendDebug('Update()', 'OnlineCount >= '.$this->GetBuffer('OnlineCount'), 0);
                }
            } else {
                $this->SendDebug('Update()', 'Sys_Ping('.$host.','.$timeout.') => false', 0);
                if ($this->GetBuffer('OnlineCount') != '0') {
                    $this->SetBuffer('OnlineCount', 0);
                    $this->SendDebug('Update()', 'OnlineCount => 0', 0);
                }

                if (intval($this->GetBuffer('ErrorCount')) >= $this->ReadPropertyInteger('RetryError')) {
                    $this->SetValue('Online', false);
                } else {
                    $this->SetBuffer('ErrorCount', intval($this->GetBuffer('ErrorCount')) + 1);
                    $this->SendDebug('Update()', 'ErrorCount => '.$this->GetBuffer('ErrorCount'), 0);
                }
            }
        }
    }
