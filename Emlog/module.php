<?

class Emlog extends IPSModule {

	public function Create() {

		parent::Create();

		$this->RegisterPropertyString("IPAddress", "emlog");
		$this->RegisterPropertyInteger("Meter", 1);
		$this->RegisterPropertyInteger("Interval", 60);

		$this->RegisterTimer("Update", $this->ReadPropertyInteger("Interval") * 1000, 'EML_Update($_IPS[\'TARGET\']);');

	}

	public function Update() {

		$data = file_get_contents("http://" . $this->ReadPropertyString("IPAddress") . "/pages/getinformation.php?export&meterindex=" . $this->ReadPropertyInteger("Meter"));
		
		//Data for testing
		//$data = '{"product":"Emlog - Electronic MeterLog","version":1.08,"Zaehlerstand_Bezug":{"Stand180":8206.0674551,"Stand181":8198.35,"Stand182":1.11},"Zaehlerstand_Lieferung":{"Stand280":0,"Stand281":0,"Stand282":0},"Wirkleistung_Bezug":{"Leistung170":1204.52,"Leistung171":168.01,"Leistung172":991.56,"Leistung173":44.95},"Wirkleistung_Lieferung":{"Leistung270":0,"Leistung271":0,"Leistung272":0,"Leistung273":0},"Kwh_Bezug":{"Kwh180":0.49692799999866,"Kwh181":0.48999999999978,"Kwh182":0},"Kwh_Lieferung":{"Kwh280":0,"Kwh281":0,"Kwh282":0},"Betrag_Bezug":{"Betrag180":0.13968646079962,"Betrag181":0,"Betrag182":0,"Waehrung":"EUR"},"Betrag_Lieferung":{"Betrag280":0,"Betrag281":0,"Betrag282":0,"Waehrung":"EUR"},"DiffBezugLieferung":{"Betrag":-0.13968646079962}}';
		
		$data = json_decode($data, true);
		
		$getName = function($prefix, $key) {
			
			switch($key) {
				case "Stand180":
					return "Bezug Gesamt (Zählerstand)";
				case "Stand181":
					return "Bezug Tarif 1 (Zählerstand)";
				case "Stand182":
					return "Bezug Tarif 2 (Zählerstand)";
				case "Stand280":
					return "Lieferung Gesamt (Zählerstand)";
				case "Stand281":
					return "Lieferung Tarif 1 (Zählerstand)";
				case "Stand282":
					return "Lieferung Tarif 2 (Zählerstand)";
				case "Kwh180":
					return "Bezug Gesamt (Kilowattstunden)";
				case "Kwh181":
					return "Bezug Tarif 1 (Kilowattstunden)";
				case "Kwh182":
					return "Bezug Tarif 2 (Kilowattstunden)";
				case "Kwh280":
					return "Lieferung Gesamt (Kilowattstunden)";
				case "Kwh281":
					return "Lieferung Tarif 1 (Kilowattstunden)";
				case "Kwh282":
					return "Lieferung Tarif 2 (Kilowattstunden)";
				case "Betrag180":
					return "Bezug Gesamt (Betrag)";
				case "Betrag181":
					return "Bezug Tarif 1 (Betrag)";
				case "Betrag182":
					return "Bezug Tarif 2 (Betrag)";
				case "Betrag280":
					return "Lieferung Gesamt (Betrag)";
				case "Betrag281":
					return "Lieferung Tarif 1 (Betrag)";
				case "Betrag282":
					return "Lieferung Tarif 2 (Betrag)";
				case "Leistung170":
					return "Wirkleistung Bezug Gesamt";
				case "Leistung171":
					return "Wirkleistung Bezug Phase 1";
				case "Leistung172":
					return "Wirkleistung Bezug Phase 2";
				case "Leistung173":
					return "Wirkleistung Bezug Phase 3";
				case "Leistung270":
					return "Wirkleistung Lieferung Gesamt";
				case "Leistung271":
					return "Wirkleistung Lieferung Phase 1";
				case "Leistung272":
					return "Wirkleistung Lieferung Phase 2";
				case "Leistung273":
					return "Wirkleistung Lieferung Phase 3";
				case "Betrag":
					return "Differenz Bezug/Lieferung (Betrag)";
			}
			
			return $key;
		};
		
		$getProfile = function($prefix, $key) {

			$startsWith = function($haystack, $needle)
			{
				$length = strlen($needle);
				return (substr($haystack, 0, $length) === $needle);
			};
			
			if($startsWith($key, "Betrag")) {
				return "Euro";
			}
			
			if($startsWith($key, "Leistung")) {
				return "Watt.14490";
			}

			if($startsWith($key, "Kwh")) {
				return "Electricity";
			}

			return "";
		}; 
		
		foreach($data as $prefix => $object) {
			if(is_array($object)) {
				foreach($object as $key => $value) {
					if($key != "Waehrung") {
						$this->RegisterVariableFloat($prefix . "_" . $key, $getName($prefix, $key), $getProfile($prefix, $key));
						$this->SetValue($prefix . "_" . $key, $value);
					}
				}
			}
		}
		
	}

}