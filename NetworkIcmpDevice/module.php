<?php

declare(strict_types=1);
    class NetworkIcmpDevice extends IPSModule
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
        }

        public function EnableLogging()
        {
            $archiveId = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];

            $arr = ['Online'];

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
            $arr = ['Online'];

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
		public function GetState()
		{
			return $this->GetValue('Online');
		}
    }
