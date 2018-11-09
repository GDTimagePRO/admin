package redemption;

import java.io.Serializable;

public class BarCodeConfig implements Serializable {
	
	public static class Genesis {
		private String code;

		public String getCode() {
			return code;
		}

		public void setCode(String code) {
			this.code = code;
		}
	}
	
	private Genesis genesis;

	public Genesis getGenesis() {
		return genesis;
	}

	public void setGenesis(Genesis genesis) {
		this.genesis = genesis;
	}
	
	
}
