#include <esp_task_wdt.h>	
#include <EEPROM.h>		

#define	SIZE		200
#define	LINELIMIT	300
#define PORTNO		5678	// local port no.
#define	TIMEOUT		2000
#define	TRUE		1
#define FALSE		0
#define SLAVE_ID	1

typedef word	WORD;
typedef	byte	BYTE;

//暫存器
#define MAX_ADDR			9	// 最大暫存器數量
#define	OP_code_ADDR		0	// 運算碼
#define LightStatus_ADDR 	1	// 照明設備狀態
#define ACSStatus_ADDR 		2	// 燈具狀態
#define WW3_TIME_ADDR 		3	// 大燈開啟時間長度		
#define WW1_TIME_ADDR		4	// 小燈開啟時間長度	
#define WR3_TIME_ADDR		5	// 警示燈開啟時間長度
#define LEDStatus_ADDR 		6	// 目前開啟燈具
#define ACS_3W_ADDR 		7	// 大燈電流
#define ACS_1W_ADDR 		8	// 小燈電流

// 運算碼
#define OP_NOP				0	// 不動作
#define OP_HUM_AUTO_ON		1	// 開啟自動感應
#define OP_HUM_AUTO_OFF		2	// 關閉自動感應
#define OP_3WW_MANUAL_ON	3	// 開啟大燈
#define OP_1WW_MANUAL_ON	4	// 開啟小燈
#define OP_3WR_MANUAL_ON	5	// 開啟警示燈
#define OP_MANUAL_OFF		6	// 關閉燈具
#define OP_EMERGENCE_TEST	7	// 警報測試
#define OP_EMERGENCE_OFF	8	// 關閉警報測試
#define OP_LED_CHECK_TEST	9	// 燈具電流檢測
#define OP_RESET			10	// 重置
#define OP_EFFECT_ON		11	// 開啟特效燈
#define OP_EFFECT_OFF		12	// 關閉特效燈
#define OP_Conn				13	// VC7300 完成組網
	

// 照明設備狀態
#define IDLE			0	// 閒置
#define LIGHT_HUM_AUTO_ON	1 // 自動感應開啟
#define LIGHT_HUM_AUTO_OFF	2 // 自動感應關閉
#define LIGHT_MANUAL_3WW_ON	3	// 大燈開啟
#define LIGHT_MANUAL_1WW_ON	4	// 小燈開啟
#define LIGHT_MANUAL_3WR_ON	5	// 警示燈開啟
#define LIGHT_MANUAL_OFF	6	// 燈具關閉
#define LIGHT_EMERGENCE		7	// 警報中
#define LIGHT_EFFECT_ON		8	// 特效燈開啟
#define LIGHT_EFFECT_OFF	9	// 特效燈關閉
#define	LIGHT_Wait_Connect	10	// 等待組網

// 燈具電流檢查
#define LED_ACS_OK			0	// 全部正常
#define LED_ACS_3WW			1	// 大燈異常
#define LED_ACS_1WW			2	// 小燈異常
#define LED_ACS_3WW_1WW		3	// 大、小燈異常
#define LED_ACS_3WR			4	// 警示燈異常
#define LED_ACS_3WR_3WW			5	// 大、警示燈異常
#define LED_ACS_3WR_1WW			6	// 小、警示燈異常
#define LED_ACS_3WR_3WW_1WW		7	// 全部異常

// 燈具開啟情形
#define LED_NOP			0	//不動作
#define LED_3WW			1	//大燈
#define LED_1WW			2	//小燈
#define LED_3WR			3	//警示燈

// 燈具開啟時間長度
unsigned long WW3_timer = 0;
unsigned long WW1_timer = 0;
unsigned long WR3_timer = 0;

// 燈具總開啟時間長度
unsigned long ER_WW3_timer = 0;
unsigned long ER_WW1_timer = 0;
unsigned long ER_WR3_timer = 0;

// 軟體中斷時間
unsigned long cnn_timer;
unsigned long EFFECT_timer = 0;
unsigned long hum_timer = 0;

// 雙核心旗標設定
bool	start_effect = false;	// 2022/08/02 set
bool	start_timer_3WW = false;
bool	start_timer_1WW = false;
bool	start_timer_3WR = false;
bool	Led_EFFECT_flag = false;
bool	Cnn_flag = false;

// 按鈕
#define Button	18			//button
unsigned long lastDebounceTime = 0;  // the last time the output pin was toggled
unsigned long debounceDelay = 50;    // the debounce time; increase if the output flickers
int buttonState;             // the current reading from the input pin
int lastButtonState = LOW;   // the previous reading from the input pin

// 繼電器和電流感測器設定
#define WW3_LED	0
#define WW1_LED	1
#define WR3_LED	2
byte Relay[3] = {22, 25, 27};
byte Acs[3] = {13, 4, 26};	//(ANALOG)	

#define Buzz	4			// 蜂鳴器
#define PIR	    19			// 人體紅外線感應

 /*
EEPROM 設定
EEPROM： 注意格式為 WORD, 一次使用兩格子(2 bytes), 因此需先宣告
EEPROM： 注意使用時須乘以 2 
*/
#define EEPROM_MAX					15	// 最大 EEPROM 數量
#define EEPROM_LightStatus			0	// 照明設備狀態
#define EEPROM_WW3_TIMER			2	// 大燈開啟時間長度
#define EEPROM_WW1_TIMER			4	// 小燈開啟時間長度
#define EEPROM_WR3_TIMER			6	// 警示燈燈開啟時間長度
#define EEPROM_MAX_3W				8	// 3 瓦燈開啟電流情形
#define EEPROM_MAX_1W				10	// 1 瓦燈開啟電流情形

// 雙核心旗標設定
TaskHandle_t 	Task1;
bool	human_Auto_flag = false;
bool	Manual_3WW_flag = false;
bool	Manual_1WW_flag = false;
bool	Manual_3WR_flag = false;
bool	hum_flag = false;
bool	EP1_flag = false;
bool	EP2_flag = false;


// VC7300 訊息設定
int		udp_index;			// 執行 AT+USOCKREG 產生的 udp socket index
typedef struct
{
	WORD	fd_value;
	WORD	remote_port;
	char	remote_address[50];
	WORD	length;				// 必須是 4 的倍數
	char	data[LINELIMIT];
} UDP_PACKET;
//警報回傳
char	emergence_send[] = "AT+USOCKSEND=0,5678,\"fe80::fdff:ffff:f45a:e22\",4,\"EEEE\"";
//組網回傳
char	cn_send[] = "AT+USOCKSEND=0,5678,\"fe80::fdff:ffff:f45a:e22\",4,\"FFFF\"";

// Modbus 設定
char		input_buf[LINELIMIT];
WORD		input_status, input_index;
unsigned long	input_timer;
BYTE		output_buf[LINELIMIT];
WORD		hold_registers[SIZE];
WORD		hold_registerstmp;
typedef struct
{
	WORD	transaction_id;
	WORD	protocol_id;
	WORD	length;		// bytes of unit_id + function_code + ...
	BYTE	unit_id;
	BYTE	function_code;
	WORD	starting_address;
	WORD	no_of_points;
	BYTE	byte_count;
	WORD	reg_values[SIZE];
} MOD_PACKET;

// Exception Code for ModBus
#define MB_ALARM	01

UDP_PACKET	recv_udp;
MOD_PACKET	recv_modbus;

// utility
BYTE to_binary(BYTE firstbyte, BYTE secondbyte)
{
	BYTE	po = 0;
	
	if (firstbyte <= '9')
		po = (firstbyte - '0');
	else	po = (firstbyte - 'A') + 10;
	
	po = po << 4;
		
	if (secondbyte <= '9')
		po |= (secondbyte - '0');
	else	po |= (secondbyte - 'A') + 10;
	
	return po;
}

// utility
void to_hexascii(BYTE tmp[2], BYTE data)
{
	BYTE	p;
	
	p = (data & 0XF0) >> 4;
	if (p <= 9)
		tmp[0] = '0' + p;
	else	tmp[0] = 'A' + p - 10;
	
	p = data & 0X0F;
	if (p <= 9)
		tmp[1] = '0' + p;
	else	tmp[1] = 'A' + p - 10;
}

// 傳送回應訊框
void send_response_packet(BYTE *job, WORD length, UDP_PACKET *udp)
{
	char	output[LINELIMIT], ttt[10];
	BYTE	tmp[2];
	
	char	data[LINELIMIT];
	int	i, len;
	
	len = 0;
	for (i = 0; i < length; i ++)
	{
		to_hexascii(tmp, job[i]);
		data[len ++] = char(tmp[0]);
		data[len ++] = char(tmp[1]);
	}
	
	while (len % 4 != 0)
	{
		data[len] = '0';
		len ++;
	}
	data[len] = '\0';
	
	sprintf(output, "AT+USOCKSEND=%d,%d,\"%s\",%d,\"%s\"",
			udp_index,
			udp->remote_port,
			udp->remote_address,
			len,
			data);
	
	/*
	strcpy(output, "AT+USOCKSEND=");
	itoa(udp_index, ttt, 10);
	strcat(output, ttt);
	strcat(output, ",");
	
	itoa(udp->remote_port, ttt, 10);
	strcat(output, ttt);
	strcat(output, ",\"");
	
	strcat(output, udp->remote_address);
	strcat(output, "\",");
	
	itoa(len, ttt, 10);
	strcat(output, ttt);
	
	strcat(output, ",\"");
	strcat(output, data);
	strcat(output, "\"");
	*/
	
	Serial2.println(output);
	
	while (! Serial2.available()) ;		// 等候 VC7300 回應

	while (! Serial2.find("OK"))
	{
		Serial2.println(output);
		delay(100);
	}
}

// 產生 exception response frame
void gen_exception_response_packet(BYTE *msg, WORD *size, BYTE except_code, MOD_PACKET *input)
{
	BYTE	j = 0;

	msg[j ++] = input->transaction_id / 256;
	msg[j ++] = input->transaction_id % 256;
	msg[j ++] = input->protocol_id / 256;
	msg[j ++] = input->protocol_id % 256;
	msg[j ++] = input->length / 256;
	msg[j ++] = input->length % 256;
	msg[j ++] = input->unit_id;
	msg[j ++] = input->function_code | 0X80;
	msg[j ++] = except_code;
	*size = j;
}

// 產生 function code 03 回應: read Holding Registers
void gen_03_response_packet(BYTE *msg, WORD *size, MOD_PACKET *input)
{
	BYTE	i, j;
	BYTE	bytecount = 0;
	WORD	data;
	
	if (input->starting_address + input->no_of_points > SIZE)
	{
		gen_exception_response_packet(msg, size, 0X02, input);
		return;
	}
	
	j = 0;
	
	msg[j ++] = input->transaction_id / 256;
	msg[j ++] = input->transaction_id % 256;
	msg[j ++] = input->protocol_id / 256;
	msg[j ++] = input->protocol_id % 256;
	
	data = 3 + input->no_of_points * 2;	// for length
	msg[j ++] = data / 256;
	msg[j ++] = data % 256;
	
	msg[j ++] = input->unit_id;
	/*
	if (input->unit_id != SLAVE_ID)
	{
		gen_exception_response_packet(msg, size, MB_NOT_OWN, input);
		return;
	}
	*/
	msg[j ++] = input->function_code;
	
	bytecount = input->no_of_points * 2;
	msg[j ++] = (BYTE) (bytecount);
	
	for (i = 0; i < input->no_of_points; i ++)
	{
		data = hold_registers[input->starting_address + i];
		msg[j ++] = data / 256;
		msg[j ++] = data % 256;
	}

	
	*size = j;
}

// 產生 function code 10 回應:  Preset Multiple Registers
void gen_10_response_packet(BYTE *msg, WORD *size, MOD_PACKET *input)
{
	BYTE	i, j;
	BYTE	bytecount = 0;
	WORD	data;
	
	if (input->starting_address + input->no_of_points > SIZE)
	{
		gen_exception_response_packet(msg, size, 0X02, input);
		return;
	}
	
	j = 0;
	
	msg[j ++] = input->transaction_id / 256;
	msg[j ++] = input->transaction_id % 256;
	msg[j ++] = input->protocol_id / 256;
	msg[j ++] = input->protocol_id % 256;
	
	data = 6;
	msg[j ++] = data / 256;		// for length
	msg[j ++] = data % 256;
	msg[j ++] = input->unit_id;
/*
	if (input->unit_id != SLAVE_ID)
	{
		gen_exception_response_packet(msg, size, MB_NOT_OWN, input);
		return;
	}
*/
	msg[j ++] = input->function_code;
	
	msg[j ++] = (BYTE) (input->starting_address / 256);
	msg[j ++] = (BYTE) (input->starting_address % 256);
	
	msg[j ++] = (BYTE) (input->no_of_points / 256);
	msg[j ++] = (BYTE) (input->no_of_points % 256);
	
	*size = j;
}

// 讀取 Serial2 輸入 (from VC7300)
void read_input()
{
	char	ch;
	ch = Serial2.read();
	
	switch(input_status)
	{
		case 0:	if (ch == '+')	// leading character
			{
				input_status = 1;
				input_index = 0;
				
				input_buf[input_index] = ch;
				input_index ++;
				
				input_timer = millis();
			}
			break;
			
		case 1: if ((ch >= '0' && ch <= '9') || (ch >= 'A' && ch <= 'F') || (ch >= 'a' && ch <= 'f')
				|| (ch == '\"')	|| (ch == ',') || (ch == ' ') || (ch == ':'))
			{
				if (ch >= 'a' && ch <= 'f')
					ch = ch - 32;
				
				input_status = 1;
				input_buf[input_index] = ch;
				input_index ++;
				
				if (input_index >= LINELIMIT)	// input buffer overrun
				{
					input_status = 0;
					input_index = 0;
				}
				
				input_timer = millis();
			}
			else if (ch == '\n')
			{
				input_status = 2;
				input_buf[input_index] = '\0';
				input_timer = millis();
			}
			break;
	}
}

// 檢查 VC7300 輸入資料並轉換成 FRAME 結構
int parse_input(char *input, UDP_PACKET *udp, MOD_PACKET *mod)
{
	char	*p, ttt[100];
	int	i, j;
	BYTE	data;
	WORD	wt;
	
	p = input;
	
	if (*p == '\0')
		return FALSE;
	
	i = 0;			// 抓取 "+USOCKRECV:"
	while (*p != ' ')
	{
		ttt[i] = *p;
		p ++;
		i ++;
		if (*p == '\0')
			return FALSE;
	}
	ttt[i] = '\0';
	p ++;
	
	//if (strcmp("+USOCKRECV:", ttt) != 0)
		//return FALSE;
	
	p ++;	// skip ' '
	
	i = 0;			// 抓取 fd_value
	while (*p != ',')
	{
		ttt[i] = *p;
		
		i ++;
		p ++;
		if (*p == '\0')
			return FALSE;
	}
	ttt[i] = '\0';
	p ++;
	
	udp->fd_value = atoi(ttt);
	
	i = 0;			// 抓取 remote_port
	while (*p != ',')
	{
		ttt[i] = *p;
		
		i ++;
		p ++;
		if (*p == '\0')
			return FALSE;
	}
	ttt[i] = '\0';
	p ++;
	
	udp->remote_port = atoi(ttt);
	
	while (*p != '\"')
	{
		p ++;
		if (*p == '\0')
			return FALSE;
	}
	p ++;
	
	i = 0;			// 抓取 remote_address
	while (*p != '\"')
	{
		ttt[i] = *p;
		
		p ++;
		i ++;
		if (*p == '\0')
			return FALSE;
	}
	ttt[i] = '\0';
	p ++;
	
	strcpy(udp->remote_address, ttt);
	
	while (*p != ',')	// 跳過 ','
	{
		p ++;
		if (*p == '\0')
			return FALSE;
	}
	p ++;
	
	i = 0;			// 抓取 length
	while (*p != ',')
	{
		ttt[i] = *p;
		
		i ++;
		p ++;
		if (*p == '\0')
			return FALSE;
	}
	ttt[i] = '\0';
	p ++;
	udp->length = atoi(ttt);
	
	while (*p != '\"')	// 尋找下一個 "
	{
		p ++;
		if (*p == '\0')
			return FALSE;
	}
	p ++;
	
	i = 0;		// 抓取 data
	while (*p != '\"')
	{
		ttt[i] = *p;
		
		p ++;
		i ++;
		if (*p == '\0')
			return FALSE;
	}
	ttt[i] = '\0';
	p ++;
	
	strcpy(udp->data, ttt);
	
	// parse MODBUS
	if (i < 10)
		return FALSE;
	
	p = udp->data;
	
	wt = to_binary(*p, *(p+1));
	p = p + 2;
	wt = wt * 256 + to_binary(*p, *(p+1));
	p = p + 2;
	mod->transaction_id = wt;
	
	wt = to_binary(*p, *(p+1));
	p = p + 2;
	wt = wt * 256 + to_binary(*p, *(p+1));
	p = p + 2;
	mod->protocol_id = wt;
	
	wt = to_binary(*p, *(p+1));
	p = p + 2;
	wt = wt * 256 + to_binary(*p, *(p+1));
	p = p + 2;
	mod->length = wt;
	
	mod->unit_id = to_binary(*p, *(p+1));
	p = p + 2;
	
	mod->function_code = to_binary(*p, *(p+1));
	p = p + 2;
	
	wt = to_binary(*p, *(p+1));
	p = p + 2;
	wt = wt * 256 + to_binary(*p, *(p+1));
	p = p + 2;
	mod->starting_address = wt;
	
	wt = to_binary(*p, *(p+1));
	p = p + 2;
	wt = wt * 256 + to_binary(*p, *(p+1));
	p = p + 2;
	mod->no_of_points = wt;
	
	switch (mod->function_code)
	{
		case 0X03: break;
		
		case 0X10:
			// get byte count, 1 bytes
			mod->byte_count = to_binary(*p, *(p+1));
			p = p + 2;
			
			if (mod->byte_count <= 0)
				return FALSE;
			
			if (i - 24 < mod->byte_count)
				return FALSE;
			
			// get registers' values in WORD
			for (i = 0; i < mod->no_of_points; i++)
			{
				wt = to_binary(*p, *(p+1));
				p = p + 2;
				wt = wt * 256 + to_binary(*p, *(p+1));
				p = p + 2;
				mod->reg_values[i] = wt;
			}
			
			break;
		default:
			return FALSE;
	}
	
	return TRUE;
}

// 初始化暫存器
void Initial_HR(void)								
{
	for (int i = 0; i < MAX_ADDR; i ++)
		hold_registers[i] = 0;
}

// 重置設定
void Reset_ESP32()
{
	byte i;
	Manual_1WW_flag = false;
	Manual_3WW_flag = false;
	Manual_3WR_flag = false;
	human_Auto_flag = false;
	hum_flag = false;
	start_effect = false;
	led_task(LED_NOP);
	for (i = 0; i < MAX_ADDR; i++ )
		hold_registers[i] = 0;
	
	for (i = 0; i < EEPROM_MAX; i++ )
		EEPROM.write(i, 0);
	EEPROM.commit();
	digitalWrite(Buzz, LOW);
}

// 初始化設定
void Initial_set(void) 
{
	Initial_HR();

	// 設定 ESP32 接腳屬性
	BYTE i,j;
	for(i = 0;i < 3;i++)
	{
		pinMode(Relay[i], OUTPUT);
	}
	pinMode(Button, INPUT_PULLDOWN);
	pinMode(Buzz, OUTPUT);
	pinMode(PIR, INPUT);
	
	// 在核心 0 啟動 Task1
	xTaskCreatePinnedToCore
	(
		Task1_senddata,
		"Task1",
		10000,
		NULL,
		0,
		&Task1,
		0
	);
	
	EEPROM.begin(EEPROM_MAX);	//設定 EEPROM 
	esp_task_wdt_init(10, false);	// 設定看門狗並關閉自動重新啟動
	
	// 開機等待 VC7300 組網
	hold_registers[LightStatus_ADDR] = LIGHT_Wait_Connect;
	
	
	// 初始化全域變數
	start_timer_3WW = false;
	start_timer_1WW = false;
	start_timer_3WR = false;
	start_effect = false;
	Cnn_flag = false;
	input_status = 0;
	input_index = 0;
	input_timer = 0;
	hold_registers[WW3_TIME_ADDR] = 0;
	hold_registers[WW1_TIME_ADDR] = 0;
	hold_registers[WR3_TIME_ADDR] = 0;
	
	// 記錄軟體時間
	cnn_timer = millis();
	
	while(Cnn_flag == 0)
	{
		if(Serial2.available())
		{	
			Wi_SUN_RECEIVE();
		}
		if(millis() - cnn_timer > 10000)
		{
			Serial2.println(cn_send);
			cnn_timer = millis();
		}
	}
}

// 檢查燈具電流
void Led_check()
{
	byte i, j=0;
	for(i=0; i<3; i++)
	{
		led_task(i + 1);
	
		delay(1000);
		
		if(i ==0 || i == 2)
		{
			if(analogRead(Acs[i]) > hold_registers[ACS_3W_ADDR])
			{
				if(i == 0)
				{
					j += 1;
				}
				else
				{
					j += i * 2;
				}
			}	
		}
		else if(i == 1)
		{
			if(analogRead(Acs[i]) > hold_registers[ACS_1W_ADDR])
			{
		
				j += i * 2;
			
			}
		}
		digitalWrite(Relay[i], LOW);
	}
	
	hold_registers[ACSStatus_ADDR] = j;
}

// 初始化 VC7300
void Initial_WI_SUN()
{
	char	ss[LINELIMIT], ttt[50], ppp[50];
	int	i, j;
	delay(3000);

	// 關閉 VC7300 重複回傳
	strcpy(ss, "AT+ECHO=0");
	Serial2.println(ss);
	delay(1000);
	
	// 註冊 socket
	strcpy(ss, "AT+USOCKREG");		// udp_index
	Serial2.println(ss);
	udp_index = 0;
	delay(1000);
	
	// 綁定本地端連接埠
	sprintf(ss, "%s%d,%d", "AT+USOCKBIND=", udp_index, PORTNO);
	Serial2.println(ss);
}

// 讀取 VC7300 輸入資料
void Wi_SUN_RECEIVE()
{
	WORD	length, i;
	
	if (Serial2.available())
		read_input();
	// 處理 VC7300 輸入資料
	if (input_status == 2)
	{
		if (parse_input(input_buf, &recv_udp, &recv_modbus) == TRUE)
		{
			switch (recv_modbus.function_code)
			{
				case 0X03:
					gen_03_response_packet(output_buf, &length, &recv_modbus);
					send_response_packet(output_buf, length, &recv_udp);
					hold_registers[WR3_TIME_ADDR] = 0;
					hold_registers[WW1_TIME_ADDR] = 0;
					hold_registers[WW3_TIME_ADDR] = 0;
					EEPROM.write(ER_WW3_timer, 0);
					EEPROM.write(ER_WW1_timer, 0);
					EEPROM.write(ER_WR3_timer, 0);
					break;
						
				case 0X10:	
					do_action();
					gen_10_response_packet(output_buf, &length, &recv_modbus);	
					send_response_packet(output_buf, length, &recv_udp);		
					break;
			}
		}		
		// reset input buffer index and status
		input_index = 0;
		input_status = 0;
	}
	
	// input timeout, reset input buffer indexpp and status
	if (input_status != 0)
	{
		if (millis() - input_timer > TIMEOUT)
		{
			input_index = 0;
			input_status = 0;
			input_timer = millis();
		}
	}
}

// 燈具開啟作業(確保僅一顆燈具開啟)
void led_task(byte ch_led) 
{
	switch(ch_led)
	{
		case LED_NOP:
			digitalWrite(Relay[WW3_LED], LOW);
			digitalWrite(Relay[WW1_LED], LOW);
			digitalWrite(Relay[WR3_LED], LOW);
			start_timer_3WW = false;
			start_timer_1WW = false;
			start_timer_3WR = false;
			break;
		case LED_3WW:
			digitalWrite(Relay[WW1_LED], LOW);
			digitalWrite(Relay[WR3_LED], LOW);
			digitalWrite(Relay[WW3_LED], HIGH);
			WW3_timer = millis();
			ER_WW3_timer = WW3_timer;
			start_timer_3WW = true;
			start_timer_1WW = false;
			start_timer_3WR = false;
			break;
		case LED_1WW:
			digitalWrite(Relay[WW3_LED], LOW);
			digitalWrite(Relay[WR3_LED], LOW);
			digitalWrite(Relay[WW1_LED], HIGH);
			WW1_timer = millis();
			ER_WW1_timer = WW1_timer;
			start_timer_3WW = false;
			start_timer_1WW = true;
			start_timer_3WR = false;
			break;
		case LED_3WR:
			digitalWrite(Relay[WW3_LED], LOW);
			digitalWrite(Relay[WR3_LED], HIGH);
			digitalWrite(Relay[WW1_LED], LOW);
			WR3_timer = millis();	
			ER_WR3_timer = WR3_timer;
			start_timer_3WW = false;
			start_timer_1WW = false;
			start_timer_3WR = true;
			break;
	}
}

// 執行動作
void do_action()
{
	WORD	i,j;	
	WORD EEPROM_GET;
	if(recv_modbus.function_code == 0X10)
	{
		if(hold_registers[LightStatus_ADDR] == LIGHT_Wait_Connect) // 等待組網
		{
			if((recv_modbus.starting_address == OP_code_ADDR) && recv_modbus.reg_values[0] == OP_Conn)
			{
				Cnn_flag = 1;
				hold_registers[LightStatus_ADDR] = IDLE;
			}
		}
		else 
		{
			if(recv_modbus.starting_address == OP_code_ADDR)
			{
				switch(recv_modbus.reg_values[0])
				{
					case OP_NOP: 
						break;
					case OP_HUM_AUTO_ON: 	// 自動感應開啟				
						Manual_1WW_flag = false;
						Manual_3WW_flag = false;
						Manual_3WR_flag = false;
						Led_EFFECT_flag = false; 
						start_effect = false;
						
						
						hold_registers[LightStatus_ADDR] = LIGHT_HUM_AUTO_ON;
						EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] /256);
						EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] %256);
						EEPROM.commit();
						human_Auto_flag = true; 						
						break;
					case OP_HUM_AUTO_OFF: 	// 自動感應關閉		
						human_Auto_flag = false; 						
						Manual_1WW_flag = false;
						Manual_3WW_flag = false;
						Manual_3WR_flag = false;
						Led_EFFECT_flag = false; 
						start_effect = false;
						
						hold_registers[LightStatus_ADDR] = LIGHT_HUM_AUTO_OFF; 	
						EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] /256);
						EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] %256);
						EEPROM.commit();	
						led_task(LED_NOP);
						break;
					case OP_3WW_MANUAL_ON: // 大燈開啟		
						human_Auto_flag = false; 						
						Manual_1WW_flag = false;
						Manual_3WR_flag = false;
						Led_EFFECT_flag = false; 
						start_effect = false;
						
						Manual_3WW_flag = true; 
						break;
					case OP_1WW_MANUAL_ON: // 小燈開啟	
						human_Auto_flag = false; 						
						Manual_3WW_flag = false;
						Manual_3WR_flag = false;
						Led_EFFECT_flag = false; 
						start_effect = false;
						
						Manual_1WW_flag = true; 
						break;
					case OP_3WR_MANUAL_ON: // 警示燈燈開啟	
						human_Auto_flag = false; 						
						Manual_1WW_flag = false;
						Manual_3WW_flag = false;
						Led_EFFECT_flag = false; 
						start_effect = false;
						
						Manual_3WR_flag = true; 
						break;
					case OP_MANUAL_OFF: 	// 燈具關閉	
						human_Auto_flag = false; 						
						Manual_1WW_flag = false;
						Manual_3WW_flag = false;
						Manual_3WR_flag = false;
						Led_EFFECT_flag = false; 
						start_effect = false;
						
						hold_registers[LightStatus_ADDR] = LIGHT_MANUAL_OFF;
						EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] /256);
						EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] %256);
						EEPROM.commit();
						led_task(LED_NOP);
						break;
					case OP_EMERGENCE_TEST: // 警報狀態測試
						human_Auto_flag = false; 						
						Manual_1WW_flag = false;
						Manual_3WW_flag = false;
						Manual_3WR_flag = false;
						Led_EFFECT_flag = false; 
						start_effect = false;
						
						hold_registers[LightStatus_ADDR] = LIGHT_EMERGENCE;
						EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] /256);
						EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] %256);
						EEPROM.commit();
						led_task(LED_3WR);
						digitalWrite(Buzz, HIGH);
						break;
					case OP_EMERGENCE_OFF: // 警報關閉
						start_effect = false;
						hold_registers[LightStatus_ADDR] = IDLE;
						EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] /256);
						EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] %256);
						EEPROM.commit();
						led_task(LED_NOP);
						digitalWrite(Buzz, LOW);
						break;
					
					case OP_LED_CHECK_TEST:  Led_check(); break;	// 燈具電流檢測
					case OP_RESET: Reset_ESP32(); break;	// 重置
					case OP_EFFECT_ON: 	//特效燈開啟
						human_Auto_flag = false; 						
						Manual_1WW_flag = false;
						Manual_3WW_flag = false;
						Manual_3WR_flag = false;
						Led_EFFECT_flag = true;  
						
						hold_registers[LightStatus_ADDR] = LIGHT_EFFECT_ON;
						EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] / 256);
						EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] % 256);
						EEPROM.commit();
						EFFECT_timer = millis(); 
						break;
					case OP_EFFECT_OFF: //特效燈關閉
						human_Auto_flag = false; 						
						Manual_1WW_flag = false;
						Manual_3WW_flag = false;
						Manual_3WR_flag = false;
						Led_EFFECT_flag = false; 
						start_effect = false;
						
						led_task(LED_NOP);
						hold_registers[LightStatus_ADDR] = LIGHT_EFFECT_OFF;
						EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] /256);
						EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] %256);
						EEPROM.commit();
						break;
					break;
				}
			}
			else
			{
				switch(recv_modbus.starting_address)
				{
					case ACS_1W_ADDR:
						hold_registers[ACS_1W_ADDR] = recv_modbus.reg_values[0];
						EEPROM.write(EEPROM_MAX_3W, hold_registers[ACS_1W_ADDR] / 256);
						EEPROM.write(EEPROM_MAX_3W + 1, hold_registers[ACS_1W_ADDR] % 256);
						EEPROM.commit();
						break;
					case ACS_3W_ADDR:
						hold_registers[ACS_3W_ADDR] = recv_modbus.reg_values[0];
						EEPROM.write(EEPROM_MAX_1W, hold_registers[ACS_3W_ADDR] / 256);
						EEPROM.write(EEPROM_MAX_1W + 1, hold_registers[ACS_3W_ADDR] % 256);
						EEPROM.commit();
						break;
				}
				
			}
		}
	}			
}

// 自動感應作業
void human_detect()
{
	if( (digitalRead(PIR) == 1) && (!hum_flag) )
	{
		led_task(LED_3WW);
		hum_flag = true;
		hum_timer = millis();
	}
	else if( (digitalRead(PIR) == 0) && (!hum_flag) )
	{
		led_task(LED_1WW);
		hum_timer = 0;
		hum_flag = false;
	}
	
	if( (hum_flag) && (digitalRead(PIR) == HIGH) )
	{
		hum_timer = millis();
	}
}

// 取得 ESP32 FLASH 資料
void	gainEEPROM()
{
	byte  EEPROM_GETData;
	BYTE  i;
	
	// 取得照明設備狀態
	hold_registers[LightStatus_ADDR] = (WORD) (EEPROM.read(EEPROM_LightStatus)) << 8;
	hold_registers[LightStatus_ADDR] += EEPROM.read(EEPROM_LightStatus + 1);
	switch(hold_registers[LightStatus_ADDR])
	{
		case LIGHT_MANUAL_1WW_ON:
			led_task(WW1_LED);
			break;
		case LIGHT_MANUAL_3WW_ON:
			led_task(WW3_LED);
			break;
		case LIGHT_MANUAL_3WR_ON:
			led_task(WR3_LED);
			break;
		case LIGHT_EMERGENCE:
			digitalWrite(Buzz, HIGH);
			led_task(WR3_LED);
			break;
		case LIGHT_HUM_AUTO_ON:
			EP1_flag = true;
			break;
		case LIGHT_EFFECT_ON:
			EP2_flag = true;
			break;
		default:
			led_task(LED_NOP);
			break;
	}
	
	// 取得大燈開啟時間長度
	hold_registers[WW3_TIME_ADDR] = (WORD) (EEPROM.read(EEPROM_WW3_TIMER)) << 8;
	hold_registers[WW3_TIME_ADDR] += EEPROM.read(EEPROM_WW3_TIMER + 1);
	
	// 取得小燈開啟時間長度
	hold_registers[WW1_TIME_ADDR] = (WORD) (EEPROM.read(EEPROM_WW1_TIMER)) << 8;
	hold_registers[WW1_TIME_ADDR] += EEPROM.read(EEPROM_WW1_TIMER + 1);
	
	// 取得警示燈燈開啟時間長度
	hold_registers[WR3_TIME_ADDR] = (WORD) (EEPROM.read(EEPROM_WR3_TIMER)) << 8;
	hold_registers[WR3_TIME_ADDR] += EEPROM.read(EEPROM_WR3_TIMER + 1);
	
	// 取得 1 瓦燈開啟電流情形
	hold_registers[ACS_1W_ADDR] = (WORD) (EEPROM.read(EEPROM_MAX_1W)) << 8;
	hold_registers[ACS_1W_ADDR] += EEPROM.read(EEPROM_MAX_1W + 1);

	// 取得 3 瓦燈開啟電流情形
	hold_registers[ACS_3W_ADDR] = (WORD) (EEPROM.read(EEPROM_MAX_3W)) << 8;
	hold_registers[ACS_3W_ADDR] += EEPROM.read(EEPROM_MAX_3W + 1);
}

// 核心 0 作業
void Task1_senddata(void * pvParameters)					// Core 0
{
	for (;;) 
	{
		byte i,j;
		
		if(Manual_1WW_flag) // 小燈開關作業
		{
			if(hold_registers[LightStatus_ADDR] != LIGHT_MANUAL_1WW_ON)
			{
				hold_registers[LightStatus_ADDR] = LIGHT_MANUAL_1WW_ON;
				EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] / 256);
				EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] % 256);
				EEPROM.commit();
				led_task(LED_1WW);
			}
			if(!Manual_1WW_flag)
				led_task(LED_NOP);
		}
		else if(Manual_3WW_flag) // 大燈開關作業
		{
			if(hold_registers[LightStatus_ADDR] != LIGHT_MANUAL_3WW_ON)
			{
				hold_registers[LightStatus_ADDR] = LIGHT_MANUAL_3WW_ON;
				EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] / 256);
				EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] % 256);
				EEPROM.commit();
				led_task(LED_3WW);
				
			}
			if(!Manual_3WW_flag)
				led_task(LED_NOP);
		}
		else if(Manual_3WR_flag) // 警示燈開關作業
		{
			if(hold_registers[LightStatus_ADDR] != LIGHT_MANUAL_3WR_ON)
			{
				hold_registers[LightStatus_ADDR] = LIGHT_MANUAL_3WR_ON;
				EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] / 256);
				EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] % 256);
				EEPROM.commit();
				led_task(LED_3WR);
			}
			if(!Manual_3WR_flag)
				led_task(LED_NOP);
		}
		else if(human_Auto_flag) // 自動感應開關作業
		{
			human_detect();
			if(human_Auto_flag == false)
				led_task(LED_NOP);
		}
		else if(Led_EFFECT_flag) // 特效燈開關作業
		{
			start_effect = true;
			if(Led_EFFECT_flag == false)
			{
				start_effect = false;
				led_task(LED_NOP);
			}
		}
		else
		{
			delay(1);	//Task1休息，delay(1)不可省略
		}
	}
}


void setup()
{
	// Initial set ESP32
	Serial.begin(115200);
	Serial2.begin(115200);	//  for AT Command
	Initial_set();
	
	// Initial VC7300
	Initial_WI_SUN();
	
	// 燈具檢測
	Led_check();
	
	// Gain EEPROM
	gainEEPROM();
}

//按鈕作業 
void EMERGENCE()	
{
	byte i;
	
	i = digitalRead(Button);
	if (i != lastButtonState) 
	{	
		lastDebounceTime = millis();
	}
	if ((millis() - lastDebounceTime) > debounceDelay) 
	{
		if (i != buttonState) 
		{
			buttonState = i;
			if (buttonState == HIGH) 
			{
				human_Auto_flag = false; 						
				Manual_1WW_flag = false;
				Manual_3WW_flag = false;
				Manual_3WR_flag = false;
				
				hold_registers[LightStatus_ADDR] = LIGHT_EMERGENCE;
				EEPROM.write(EEPROM_LightStatus, hold_registers[LightStatus_ADDR] / 256);
				EEPROM.write(EEPROM_LightStatus + 1, hold_registers[LightStatus_ADDR] % 256);
				EEPROM.commit();
				
				led_task(LED_3WR);
				digitalWrite(Buzz, HIGH);
				Serial2.println(emergence_send);
			}
			//buttonState = LOW;
		}
	}
	lastButtonState = i;
}


void loop()
{
	unsigned long ms = millis();
	int i = 0,j = 0;
	
	// 確認斷線後是否前狀態為自動人流
	if(EP1_flag == true)
	{
		hum_flag = false;
		Led_EFFECT_flag = false; 
		human_Auto_flag = true; 
		EP1_flag = false;
	}
	if(EP2_flag == true)
	{
		hum_flag = false;
		human_Auto_flag = false; 
		Led_EFFECT_flag = true; 
		EP2_flag = false;
	}
	
	EMERGENCE();	//查看按鈕是否被觸發
	
	// 讀取 VC7300 輸入資料
	if(Serial2.available())
	{	
		Wi_SUN_RECEIVE();
	}
	
	// 紀錄開啟時間長度
	if(start_timer_3WW)
	{
		if((ms - WW3_timer) > 1000)	// 每 1 秒記錄一次大燈開啟時間
		{
			hold_registers[WW3_TIME_ADDR] += 1;
			WW3_timer = ms;
		}
		if((ms - ER_WW3_timer) > 30000)	//每 30 秒寫入 ESP32 Flash 一次
		{
			EEPROM.write( (EEPROM_WW3_TIMER) , (hold_registers[WW3_TIME_ADDR] / 256) );
			EEPROM.write( (EEPROM_WW3_TIMER + 1) , (hold_registers[WW3_TIME_ADDR] % 256) );
			EEPROM.commit();
		}
	}
	if(start_timer_1WW)
	{
		if((ms - WW1_timer) > 1000)	// 每 1 秒記錄一次小燈開啟時間
		{
			hold_registers[WW1_TIME_ADDR] += 1;
			WW1_timer = ms;
		}
		if((ms - ER_WW1_timer) > 30000)	//每 30 秒寫入 ESP32 Flash 一次
		{
			EEPROM.write( (EEPROM_WW1_TIMER) , (hold_registers[WW1_TIME_ADDR] / 256) );
			EEPROM.write( (EEPROM_WW1_TIMER + 1) , (hold_registers[WW1_TIME_ADDR] % 256) );
			EEPROM.commit();
		}
	}
	if(start_timer_3WR)
	{
		if((ms - WR3_timer) > 1000)	// 每 1 秒記錄一次警示燈開啟時間
		{
			hold_registers[WR3_TIME_ADDR] += 1;
			WR3_timer = ms;
		}
		if((ms - ER_WR3_timer) > 30000)	//每 30 秒寫入 ESP32 Flash 一次
		{
			EEPROM.write( (EEPROM_WR3_TIMER) , (hold_registers[WR3_TIME_ADDR] / 256) );
			EEPROM.write( (EEPROM_WR3_TIMER + 1) , (hold_registers[WR3_TIME_ADDR] % 256) );
			EEPROM.commit();
		}
	}
	
	if((millis() - hum_timer) > 10000)	// 每 10 秒更新一次自動感應
	{
		hum_flag = false;
	}
	
	if(start_effect)	// 每 0.5 秒切換燈具開啟
	{
		if((millis() - EFFECT_timer) > 500)	
		{
			while(i == j)
			{
				i = rand() % 3 + 1;	
			}
			j = i;
			led_task(i);
			EFFECT_timer = millis();
		}
	}
}