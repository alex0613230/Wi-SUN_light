// sudo gcc -DDEBUG modb_2022_11_24.c -o modb_2022_11_24 -I/usr/include/mysql -lmysqlclient
// sudo gcc  modb_2022_11_24.c -o modb_2022_11_24 -I/usr/include/mysql -lmysqlclient
#include <sys/types.h>       /* basic system data types */
#include <sys/time.h>        /* timeval{} for select() */
#include <time.h>            /* timespec{} for pselect() */
#include <errno.h>
#include <fcntl.h>           /* for nonblocking */
#include <signal.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/wait.h>
#include <sys/ioctl.h>
#include <termios.h>
#include <mysql.h>

#define Serial_7O1	1
#define Serial_7E1	2
#define Serial_7N2	3
#define Serial_8N1	4
#define Serial_8O1	5
#define Serial_8E1	6
#define Serial_8N2	7

#define WW3_TIME	0
#define WW1_TIME	1
#define WR3_TIME	2
// Used Type Definitions
typedef unsigned char 	BYTE;
typedef unsigned short	WORD;

// Parameters used in mysql_real_connect()
static char *host_name = NULL; 		// server host (default=localhost)
static char *user_name = "light"; 	// username (default=login name)
static char *password = "light"; 	// password (default=none)
static unsigned int port_num = 0; 	// port number (use built-in value)
static char *socket_name = NULL; 	// socket name (use built-in value)
static char *db_name = "light"; 		// database name (default=none)
static unsigned int flags = 0; 		// connection flags (none)
static MYSQL *con;			// pointer to connection handler

#define FALSE	0
#define TRUE	1
#define SIZE	200

#define MAXLINE	1000	// max text line length

#ifndef HAVE_BZERO
#define bzero(ptr,n)        memset (ptr, 0, n)
#endif

// Definition of Modbus/TCP packet format
typedef struct
{
	WORD	transaction_id;
	WORD	protocol_id;
	WORD	length;
	BYTE	unit_id;
	BYTE	function_code;
	WORD	start_address;
	WORD	no_of_points;
	BYTE	byte_count;
	WORD	reg_values[SIZE];
	BYTE	exception_code;
} MOD_PACKET;

// Definition of AT Command Fields for AT+USOCKSEND & AT+USOCKRECV
typedef struct
{
	WORD	fd_value;
	WORD	remote_port;
	char	remote_address[50];
	WORD	length;				// 必須是 4 的倍數
	char	data[MAXLINE];
} UDP_PACKET;

int	udp_index;				// 執行 AT+USOCKREG 產生的 udp socket index

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

int open_and_initialize_device(char *device, int baudrate, int encoding)
{
	struct termios	newtio;
	int	fd;			// file descriptor for serial port
	struct flock fl;		// definition for file lock
		
	// open the device to be non-blocking (read will return immediatly) and locked
	fl.l_type = F_WRLCK; // F_RDLCK, F_WRLCK, F_UNLCK 
	fl.l_whence = SEEK_SET; // SEEK_SET, SEEK_CUR, SEEK_END
	fl.l_start = 0; // Offset from l_whence 
	fl.l_len = 0; // length，0 = to EOF
	fl.l_pid = getpid(); // our PID
	
	fd = open(device, O_RDWR | O_NOCTTY | O_NONBLOCK);
	if (fd < 0)
		return fd;
	
	fcntl(fd, F_SETLKW, &fl);  // lock the device
	// The FNDELAY option causes the read function to return 0 if no characters are available on the port.
	fcntl(fd, F_SETFL, FNDELAY);	 
		
	// set new port settings for canonical input processing
	bzero(&newtio, sizeof(newtio));

	// BAUDRATE: Set bps rate
	// CS8     : 8 data bits
	// CLOCAL  : local connection, no modem control
	// CREAD   : enable receiving characters
	switch(baudrate)
	{
		case 1200: newtio.c_cflag = B1200; break;
		case 2400: newtio.c_cflag = B2400; break;
		case 4800: newtio.c_cflag = B4800; break;
		case 9600: newtio.c_cflag = B9600; break;
		case 19200: newtio.c_cflag = B19200; break;
		case 38400: newtio.c_cflag = B38400; break;
		case 57600: newtio.c_cflag = B57600; break;
		case 115200: newtio.c_cflag = B115200; break;
		default: newtio.c_cflag = B38400; break;
	}
	
	switch(encoding)
	{
		case Serial_7O1: // 7O1
			newtio.c_cflag |= CS7 | CLOCAL | CREAD | PARENB | PARODD;
			newtio.c_cflag &= ~CSTOPB;
			break;
		case Serial_7E1: // 7E1
			newtio.c_cflag |= CS7 | CLOCAL | CREAD | PARENB;
			newtio.c_cflag &= ~CSTOPB;		// 1 stop bit
			newtio.c_cflag &= ~PARODD;
			break;
		case Serial_7N2: // 7N2
			newtio.c_cflag |= CS7 | CLOCAL | CREAD;
			newtio.c_cflag |= CSTOPB;		// 2 stop bits
			break;
		case Serial_8N1: // 8N1
			newtio.c_cflag |= CS8 | CLOCAL | CREAD;
			newtio.c_cflag &= ~CSTOPB;		// 1 stop bit
			break;
		case Serial_8O1: // 8O1
			newtio.c_cflag |= CS8 | CLOCAL | CREAD | PARENB | PARODD;
			newtio.c_cflag &= ~CSTOPB;
			break;
		case Serial_8E1: // 8E1
			newtio.c_cflag |= CS8 | CLOCAL | CREAD | PARENB;
			newtio.c_cflag &= ~CSTOPB;		// 1 stop bit
			newtio.c_cflag &= ~PARODD;
			break;
		case Serial_8N2: // 8N2
			newtio.c_cflag |= CS8 | CLOCAL | CREAD;
			newtio.c_cflag |= CSTOPB;		// 2 stop bits
			break;
		default: // 8N1, Serial_8N1
			newtio.c_cflag |= CS8 | CLOCAL | CREAD;
			newtio.c_cflag &= ~CSTOPB;		// 1 stop bit
			break;
	}

	newtio.c_iflag = IGNPAR;	// IGNPAR  : ignore bytes with parity errors
	newtio.c_oflag = 0;		// Raw output
	newtio.c_lflag = 0;		// set input mode (non-canonical, no echo,...)
	newtio.c_cflag &= ~CRTSCTS;	// disable hardware flow control

	// initialize all control characters 
	newtio.c_cc[VTIME]    = 0;   /* inter-character timer unused */
        newtio.c_cc[VMIN]     = 0;   /* non-blocking read */

	// clean the serial port buffer and activate the settings for the port 
	tcflush(fd, TCIOFLUSH);
	tcsetattr(fd, TCSANOW, &newtio);

	return fd;
}

/////////////////////////////////////////////////////////////////////////////////////
// 讀取 Serial Port 資料部份
/////////////////////////////////////////////////////////////////////////////////////

static int read_cnt;
static BYTE *read_ptr;
static BYTE read_buf[MAXLINE];

BYTE my_serial_read(int fd, BYTE *ptr)
{
	fd_set	readfs;			// file descriptor set
	int	res_select;		// for select()
	struct timeval timeout;		// for select() timeout
	
        if (read_cnt <= 0)
        {
		again:
		if ((read_cnt = read(fd, read_buf, sizeof(read_buf))) < 0)
		{
			if (errno == EINTR)
				goto again;

			return (-1);
		}
		else if (read_cnt == 0)
			return (0);

		read_ptr = read_buf;
	}

        read_cnt--;
        *ptr = *read_ptr++;

        return (1);
}

int Serial_Available(int fd)
{
	int	bytes;
	
	ioctl(fd, FIONREAD, &bytes);
	return bytes;
}

// return input_buffer in ASCII format
BYTE	input_status;
WORD	input_index;
BYTE	input_buf[MAXLINE];

void Serial_Readline(int fd)
{
        BYTE    ch, rc;
	
	while ((rc = my_serial_read(fd, &ch)) == 1)
	{		
		// printf("[%02X]", ch);
		
		switch(input_status)
		{
			case 0:	if (ch == '+')	// leading character
				{
					input_status = 1;
					input_index = 0;
					
					input_buf[input_index] = ch;
					input_index ++;
				}
				break;
				
			case 1: if ((ch >= '0' && ch <= '9')
					|| (ch >= 'A' && ch <= 'Z') || (ch >= 'a' && ch <= 'z')
					|| (ch == '\"')	|| (ch == ',') || (ch == ' ') || (ch == ':'))
				{
					if (ch >= 'a' && ch <= 'z')
						ch = ch - 32;
					
					input_status = 1;
					input_buf[input_index] = ch;
					input_index ++;
					
					if (input_index >= MAXLINE)	// input buffer overrun
					{
						input_status = 0;
						input_index = 0;
					}
				}
				else if (ch == 0X0D)		// input terminated normal
				{
					input_status = 2;
					input_buf[input_index] = '\0';
				}
				break;
			
			case 2: if (ch == 0X0A)		// input terminated normal
				{
					input_status = 3;
				}
				break;
		}
		
		if (input_status == 3)
			break;
	}
}

BYTE at_command_decap(BYTE *msg, UDP_PACKET *udp)
{
	BYTE	*p, tmp_buf[MAXLINE];
	BYTE	i;
	
	// printf("Msg: %s\n", msg);
	
	p = msg;
	while ((*p != ':') && (*p != '\0'))	p ++;	// Skip AT+USOCKRECV:
	if (*p == '\0')	return FALSE;
		
	while ((*p == ' ') && (*p != '\0'))	p ++;	// Skip space
	if (*p == '\0')	return FALSE;
	
	i = 0;						// Get socket index
	while ((*p != ',') && (*p != '\0'))
	{
		tmp_buf[i] = *p;
		p ++;
		i ++;
	}
	if (*p == '\0')	return FALSE;
	
	tmp_buf[i] = '\0';
	udp->fd_value = atoi(tmp_buf);
	
	// printf("fd_value: %s\n", tmp_buf);
	
	while ((*p == ',') && (*p != '\0'))	p ++;	// Skip ','
	if (*p == '\0')	return FALSE;
	
	while ((*p == ' ') && (*p != '\0'))	p ++;	// Skip space
	if (*p == '\0')	return FALSE;
	
	i = 0;						// Get remote port
	while ((*p != ',') && (*p != '\0'))
	{
		tmp_buf[i] = *p;
		p ++;
		i ++;
	}
	if (*p == '\0')	return FALSE;
	
	tmp_buf[i] = '\0';
	udp->remote_port = atoi(tmp_buf);
	
	// printf("Remot port: %s\n", tmp_buf);
	
	while ((*p == ',') && (*p != '\0'))	p ++;	// Skip ','
	if (*p == '\0')	return FALSE;
	
	while ((*p == ' ') && (*p != '\0'))	p ++;	// Skip space
	if (*p == '\0')	return FALSE;
	
	while ((*p == '\"') && (*p != '\0'))	p ++;	// Skip \"
	if (*p == '\0')	return FALSE;
	
	i = 0;						// Get remote address
	while ((*p != '\"') && (*p != '\0'))
	{
		tmp_buf[i] = *p;
		p ++;
		i ++;
	}
	if (*p == '\0')	return FALSE;
	
	tmp_buf[i] = '\0';
	strcpy(udp->remote_address, tmp_buf);
	
	while ((*p == '\"') && (*p != '\0'))	p ++;	// Skip \"
	if (*p == '\0')	return FALSE;
	
	while ((*p == ',') && (*p != '\0'))	p ++;	// Skip ','
	if (*p == '\0')	return FALSE;
	
	while ((*p == ' ') && (*p != '\0'))	p ++;	// Skip space
	if (*p == '\0')	return FALSE;
	
	i = 0;						// Get message length
	while ((*p != ',') && (*p != '\0'))
	{
		tmp_buf[i] = *p;
		p ++;
		i ++;
	}
	if (*p == '\0')	return FALSE;
	
	tmp_buf[i] = '\0';
	udp->length = atoi(tmp_buf);
	
	while ((*p == ',') && (*p != '\0'))	p ++;	// Skip ','
	if (*p == '\0')	return FALSE;
	
	while ((*p == ' ') && (*p != '\0'))	p ++;	// Skip space
	if (*p == '\0')	return FALSE;
	
	while ((*p == '\"') && (*p != '\0'))	p ++;	// Skip \"
	if (*p == '\0')	return FALSE;
	
	i = 0;						// Get message
	while ((*p != '\"') && (*p != '\0'))
	{
		tmp_buf[i] = *p;
		p ++;
		i ++;
	}
	if (*p == '\0')	return FALSE;
	
	tmp_buf[i] = '\0';
	strcpy(udp->data, tmp_buf);
	
	return TRUE;
}

BYTE modbus_response_decap(BYTE *msg, MOD_PACKET *modpkt)
{
	BYTE	*p;
	BYTE	data1, data2;
	
	WORD	len, i;
	
	// printf("modbus_response_decap: Msg: %s\n", msg);
	
	p = msg;
	
	modpkt->transaction_id = 0;
	modpkt->protocol_id = 0;
	modpkt->length = 0;
	modpkt->unit_id = 0;
	modpkt->function_code = 0;
	modpkt->start_address = 0;
	modpkt->no_of_points = 0;
	modpkt->byte_count = 0;
	modpkt->exception_code = 0;
	
	len = strlen(msg);
	if (len < 8)
		return FALSE;
	
	// get transaction_id, 4 characters
	data1 = to_binary(*p, *(p+1));
	p = p + 2;
	data2 = to_binary(*p, *(p+1));
	p = p + 2;
	modpkt->transaction_id = (WORD) data1 * 256 + data2;
	
	// get protocol_id, 4 characters
	data1 = to_binary(*p, *(p+1));
	p = p + 2;
	data2 = to_binary(*p, *(p+1));
	p = p + 2;
	modpkt->protocol_id = (WORD) data1 * 256 + data2;
	
	// get length, 4 characters
	data1 = to_binary(*p, *(p+1));
	p = p + 2;
	data2 = to_binary(*p, *(p+1));
	p = p + 2;
	modpkt->length = (WORD) data1 * 256 + data2;
	
	len = len - 6;
	if (len < modpkt->length)
		return FALSE;
	
	if (len < 5)
		return FALSE;
	
	// get unit_id, 2 characters
	data1 = to_binary(*p, *(p+1));
	p = p + 2;
	modpkt->unit_id = data1;
	
	// get function_code, 2 characters
	data1 = to_binary(*p, *(p+1));
	p = p + 2;
	modpkt->function_code = data1;
	
	if ((modpkt->function_code & 0X80) != 0)	// Exception
	{
		// get exception_code, 2 characters
		data1 = to_binary(*p, *(p+1));
		p = p + 2;
		modpkt->exception_code = data1;
		
		return TRUE;
	}
	
	len = len - 4;
	
	switch (modpkt->function_code)
	{
		case 0X03:	// for read Holding Registers
			// get byte_count, 2 characters
			data1 = to_binary(*p, *(p+1));
			p = p + 2;
			modpkt->byte_count = data1;
			
			if (len < modpkt->byte_count)
				return FALSE;

			// get 16-bit Registers' values
			for (i = 0; i < modpkt->byte_count / 2; i++)
			{					
				// get Registers' values, 4 characters
				data1 = to_binary(*p, *(p+1));
				p = p + 2;
				data2 = to_binary(*p, *(p+1));
				p = p + 2;
				modpkt->reg_values[i] = (WORD) data1 * 256 + data2;
			}
			
			break;
			
		case 0X10:	// for Preset Multiple Registers
			if (len < 8)
				return FALSE;
			
			// get start_address, 4 characters
			data1 = to_binary(*p, *(p+1));
			p = p + 2;
			data2 = to_binary(*p, *(p+1));
			p = p + 2;
			modpkt->start_address = (WORD) data1 * 256 + data2;
					
			// get no_of_points, 4 characters
			data1 = to_binary(*p, *(p+1));
			p = p + 2;
			data2 = to_binary(*p, *(p+1));
			p = p + 2;
			modpkt->no_of_points = (WORD) data1 * 256 + data2;
							
			break;
	}
	
	return TRUE;
}

/////////////////////////////////////////////////////////////////////////////////////
// 寫出資料至 Serial Port 部份
/////////////////////////////////////////////////////////////////////////////////////

int my_serial_write(int fd, BYTE *vptr, int n)
{
        int nleft;
        int nwritten;
        BYTE *ptr;

        ptr = vptr;
        nleft = n;
        while (nleft > 0)
        {
                if ((nwritten = write(fd, ptr, nleft)) <= 0)
                {
                        if (nwritten < 0 && errno == EINTR)
                                nwritten = 0;   // and call write() again
                        else    return (-1);    // error
                }

                nleft -= nwritten;
                ptr += nwritten;
        }

        return (n);
}

// For commands: 0X03: Read Holding Registers
void gen_03_request_packet(BYTE *msg, WORD *size, MOD_PACKET *pkt)
{
	WORD	j = 0;
	
	msg[j ++] = (BYTE) (pkt->transaction_id / 256);
	msg[j ++] = (BYTE) (pkt->transaction_id % 256);
	
	msg[j ++] = (BYTE) (pkt->protocol_id / 256);
	msg[j ++] = (BYTE) (pkt->protocol_id % 256);
	
	msg[j ++] = (BYTE) (6 / 256);	// length
	msg[j ++] = (BYTE) (6 % 256);
	
	msg[j ++] = pkt->unit_id;			// slave address
	msg[j ++] = 0X03;				// function_code
	msg[j ++] = (BYTE) (pkt->start_address / 256);	// start address
	msg[j ++] = (BYTE) (pkt->start_address % 256);
	msg[j ++] = (BYTE) (pkt->no_of_points / 256);	// no. of points
	msg[j ++] = (BYTE) (pkt->no_of_points % 256);
	
	*size = j;
}

// For commands: 0X10: Preset Multiple Registers
void gen_10_request_packet(BYTE *msg, WORD *size, MOD_PACKET *pkt)
{
	WORD	len, i, j;
	
	j = 0;
	
	msg[j ++] = (BYTE) (pkt->transaction_id / 256);
	msg[j ++] = (BYTE) (pkt->transaction_id % 256);
	
	msg[j ++] = (BYTE) (pkt->protocol_id / 256);
	msg[j ++] = (BYTE) (pkt->protocol_id % 256);
	
	len = 1 + 1 + 2 + 2 + 1 + pkt->no_of_points * 2;
	msg[j ++] = (BYTE) (len / 256);	// length
	msg[j ++] = (BYTE) (len % 256);
	
	msg[j ++] = pkt->unit_id;			// slave address
	msg[j ++] = 0X10;				// function_code
	msg[j ++] = (BYTE) (pkt->start_address / 256);	// start address
	msg[j ++] = (BYTE) (pkt->start_address % 256);
	msg[j ++] = (BYTE) (pkt->no_of_points / 256);	// no. of points
	msg[j ++] = (BYTE) (pkt->no_of_points % 256);
	msg[j ++] = (BYTE) (pkt->no_of_points * 2);	// Byte Count

	for (i = 0; i < pkt->no_of_points; i ++)
	{
		msg[j ++] = (BYTE) (pkt->reg_values[i] / 256);
		msg[j ++] = (BYTE) (pkt->reg_values[i] % 256);
	}
	
	*size = j;
}

void at_command_encap(BYTE *buf, WORD index, WORD port, BYTE *address, BYTE *mb_data, WORD size)
{
	BYTE	tmp[2], pp, tmp_buf[MAXLINE];
	WORD	i = 0, j;
	
	// Convert ModBus packet data to Hex ASCII
	i = 0;
	for (j = 0; j < size; j ++)
	{
		to_hexascii(tmp, mb_data[j]);
		tmp_buf[i ++] = tmp[0];
		tmp_buf[i ++] = tmp[1];
	}
		
	// padding '0'
	while ((i % 4) != 0)
	{
		tmp_buf[i ++] = '0';
	}
	tmp_buf[i ++] = '\0';
	
	sprintf(buf, "AT+USOCKSEND=%d,%d,\"%s\",%d,\"%s\"",
			index, port, address, strlen(tmp_buf), tmp_buf);
			
	#ifdef DEBUG
	printf("at_command_encap(): AT Command Generated = [%s]\n", buf);
	#endif
}
	
int send_udp_packet_to_serial(int fd, BYTE *packet)
{
	WORD	length, i;
	BYTE	buf[MAXLINE];
	
	#ifdef DEBUG
	printf("send_udp_packet_to_device(): packet= [%s]\n", packet);
	#endif
	
	strcpy(buf, packet);
	
	length = strlen(packet);
	// append 0X0D, 0X0A
	buf[length ++] = 0X0D;
	buf[length ++] = 0X0A;

	i = my_serial_write(fd, buf, length);
	
	if (length != i)
		return FALSE;
	return TRUE;
}

/////////////////////////////////////////////////////////////////////////////////////
// millis()
/////////////////////////////////////////////////////////////////////////////////////

struct timespec start_time;
unsigned long database_timer;
unsigned long light_timer;
unsigned long les_timer;
unsigned long check_timer;
unsigned long millis()
{
	struct timespec cur_time;
	long	ns = 0;
	long	s = 0;
	
	clock_gettime(CLOCK_REALTIME, &cur_time);
	
	ns = cur_time.tv_nsec - start_time.tv_nsec;
	if (ns < 0)
	{
		ns = ns + 1000000000L;
		s = s - 1;
	}
	
	s = s + cur_time.tv_sec - start_time.tv_sec;

	return ((unsigned long) (s * 1000 + ns / 1000000));
}

int send_to_device(int fd, BYTE *job, WORD length)
{
	WORD	i, j;
	BYTE	data[300];
	
	j = 0;
	for (i = 0; i < length; i ++)
	{
		data[j] = job[i];
		j ++;
	}
	
	// append 0X0D, 0X0A
	// data[j ++] = 0X0D;
	// data[j ++] = 0X0A;	
	data[j ++] = '\n';
		
	#ifdef DEBUG1
	printf("send_frame_to_device_ASCII(): Frame= [");
	for (i = 0; i < j - 1; i ++)
		printf("%c", data[i]);
	printf("]\n");
	#endif

	i = my_serial_write(fd, data, j);
	
	if (j != i)
		return FALSE;
	return TRUE;
}

void finish_with_error(MYSQL *con)
{
	printf("%s\n", mysql_error(con));
	mysql_close(con);
	exit(1);
}

int main(int argc, char **argv)
{

	BYTE	buf[MAXLINE], response[MAXLINE], Command[MAXLINE], output_buf[MAXLINE], mb_buf[MAXLINE], database_W[MAXLINE], tmp[MAXLINE];
	MOD_PACKET	mod_pkt;
	UDP_PACKET	udp_pkt;
	char sqlstring[300], TS[MAXLINE][50], OT[MAXLINE][MAXLINE], Time_02hms[3][MAXLINE], IP[MAXLINE][50], Wi_Sun[MAXLINE];
	int	serial_fd, res, num_fields, i, j=0,k,time_hms;
	MYSQL_RES *result;
	MYSQL_ROW row;
	BYTE	*p,size, t1, t2, len, Command_flag;
	
	// 記錄起始時間
	clock_gettime(CLOCK_REALTIME, &start_time);
	database_timer = millis();
	light_timer    = millis();
	les_timer      = millis();
	// open device, 115200 bps, 8N1
	serial_fd = open_and_initialize_device("/dev/ttyUSB0", 115200, Serial_8N1);
	if (serial_fd < 0)
	{
		printf("Serial port open error: %s\n", "/dev/ttyUSB0");
		exit(0);
	}
	// open mysql
	if ((con = mysql_init(NULL)) == NULL) 
	{
		printf("%s\n", mysql_error(con));
		exit(1);
	}

	if (mysql_real_connect(con, host_name, user_name, password,
			db_name, port_num, socket_name, flags) == NULL) 
	{
		finish_with_error(con);
	}
	
	strcpy(sqlstring, "SELECT DISTINCT IP FROM light");	
			
	if (! mysql_query(con, sqlstring))
	{
		result = mysql_store_result(con);
	
		if(result != NULL)
		{
			while(row = mysql_fetch_row(result))
			{
				sprintf(IP[j], "%s", row[0]);
				j ++;
			}
			mysql_free_result(result);
		}
	}
	for(i=0; i<j; i++)
	{
		
		//sprintf(sqlstring, "INSERT INTO Command VALUE('FFFF','%s','0000000A01100000000102000900',0,NOW())",IP[i]);	
		sprintf(sqlstring, "UPDATE Time SET LES=8 WHERE IP = '%s' ORDER BY systime DESC",IP[i]);
		if ( mysql_query(con, sqlstring))
			printf("Write RRROR %s", sqlstring);
		sprintf(sqlstring, "UPDATE light SET LIS='10' WHERE IP = '%s'",IP[i]);
		if ( mysql_query(con, sqlstring))
			printf("Write RRROR %s", sqlstring);
		
	}
	
	sprintf(sqlstring, "DELETE FROM Command WHERE flag=0",IP[i]);
		if ( mysql_query(con, sqlstring))
			printf("Write RRROR %s", sqlstring);
	while (1)
	{
		if (Serial_Available(serial_fd))
			Serial_Readline(serial_fd);
		
		if (input_status == 3)
		{
			#ifdef DEBUG
				for (i = 0; i < input_index; i ++)
					printf("%c", input_buf[i]);
				printf("\n");
			#endif
			
			res = at_command_decap(input_buf, &udp_pkt);	// 剖析 Serial Port 輸入的資料
			if (res == TRUE)
			{
				
				/*
				printf("fd_value: %d\n", udp_pkt.fd_value);
				printf("remote_port: %d\n", udp_pkt.remote_port);
				printf("remote_address: %s\n", udp_pkt.remote_address);
				printf("length: %d\n", udp_pkt.length);
				printf("data: %s\n", udp_pkt.data);
				*/
				if (strcmp(udp_pkt.data, "EEEE") == 0)
				{
					// 緊急事件
					//printf("Emergence\n");
					
					sprintf(sqlstring, "INSERT INTO EME VALUE(0,'%s', 0, NOW(),0)", 
								udp_pkt.remote_address);
													
					if ( mysql_query(con, sqlstring))
							printf("Write RRROR %s", sqlstring);
					/*
					//sprintf(sqlstring, "update Command set flag = 1  where TS = '%04X'", 
					sprintf(sqlstring, "UPDATE light set LIS = 7 where IP = '%s'", 
								udp_pkt.remote_address);
					if ( mysql_query(con, sqlstring))
							printf("Write RRROR %s", sqlstring);*/
					
				}
				else if (strcmp(udp_pkt.data, "FFFF") == 0)
				{
					sprintf(sqlstring, "UPDATE light set LIS= \'0\' WHERE IP=\'%s\'",
												udp_pkt.remote_address);
					if ( mysql_query(con, sqlstring))
							printf("Write RRROR %s", sqlstring);
	
					sprintf(sqlstring, "INSERT INTO Command VALUES(\'%d\',\'%s\',\'0000000901100000000102000D00\',  0, NOW())",
												0,udp_pkt.remote_address);
					if ( mysql_query(con, sqlstring))
						printf("Write RRROR %s", sqlstring);
				}
				else
				{
					// Modbus 正常回覆
					//printf("Normal response:\n");
					res = modbus_response_decap(udp_pkt.data, &mod_pkt);
					
					if (res == TRUE)
					{
						
						/*
						printf("transaction_id: %04X\n", mod_pkt.transaction_id);
						printf("protocol_id: %04X\n", mod_pkt.protocol_id);
						printf("length: %04X\n", mod_pkt.length);
						printf("unit_id: %02X\n", mod_pkt.unit_id);
						printf("function_code: %02X\n", mod_pkt.function_code);
						*/
						
						switch (mod_pkt.function_code)
						{
							case 0X03: 
									/*
										printf("byte_count: %02X\n", mod_pkt.byte_count);

										for (i = 0; i < mod_pkt.byte_count / 2; i ++)
										{
											printf("reg_values[%d]: %04X\n", i, mod_pkt.reg_values[i]);
											sprintf(tmp, "%04X", mod_pkt.reg_values[i]);
										}
									*/	
									//------------------讀取後 UPDATE light.LIS 
									sprintf(sqlstring, "update light set LIS = '%X' where IP = '%s'",
												mod_pkt.reg_values[0],udp_pkt.remote_address);

									printf("0x03 Response:%s\n",sqlstring);
									
									if ( mysql_query(con, sqlstring))
										printf("Write RRROR %s\n", sqlstring);
										
									
									
									//------------------讀取後 INSERT  SID, IP, LES, Time.WW3, Time.WW1, Time.3WW, NOW()
									time_hms = mod_pkt.reg_values[2];
									sprintf(Time_02hms[WW3_TIME], "%02d%02d%02d", time_hms/3600, 
										time_hms%3600/60, time_hms%3600%60);
									time_hms = mod_pkt.reg_values[3];
									sprintf(Time_02hms[WW1_TIME], "%02d%02d%02d", time_hms/3600, 
										time_hms%3600/60, time_hms%3600%60);
									time_hms = mod_pkt.reg_values[4];
									sprintf(Time_02hms[WR3_TIME], "%02d%02d%02d", time_hms/3600, 
										time_hms%3600/60, time_hms%3600%60);
									
									sprintf(sqlstring, "insert into Time values(0,'%s','%X','%s','%s','%s',NOW())",
														udp_pkt.remote_address,mod_pkt.reg_values[1],
														Time_02hms[WW3_TIME], Time_02hms[WW1_TIME],
														Time_02hms[WR3_TIME]);
														
														
									
									if ( mysql_query(con, sqlstring))
										printf("Write RRROR %s\n", sqlstring);
									
									
									
									//------------------確定接收資料後 UPDATE Command flag = 1
									//sprintf(sqlstring, "update Command set flag = 1  where TS = '%04X'", 
									//	mod_pkt.transaction_id);
									
									sprintf(sqlstring, "update Command set flag = 1  where TS = '%4X'", 
										mod_pkt.transaction_id);
									
									if ( mysql_query(con, sqlstring))
										printf("Write RRROR %s\n", sqlstring);
								
								break;
								
							case 0X10:
								//sprintf(sqlstring, "update Command set flag = 1  where TS = '%04X'", 
								//		mod_pkt.transaction_id);

								sprintf(sqlstring, "update Command set flag = 1  where TS = '%X'", 
										mod_pkt.transaction_id);
										
										
								if ( mysql_query(con, sqlstring))
									printf("Write RRROR %s\n", sqlstring);
								break;
						}						
					}
				}
			}
			
			input_index = 0;
			input_status = 0;
		}
		if(millis() - les_timer > 1800000)	//電流測試
		{
			j = 0;
			strcpy(sqlstring, "SELECT DISTINCT IP FROM light");	
			
			if (! mysql_query(con, sqlstring))
			{
				result = mysql_store_result(con);
			
				if(result != NULL)
				{
					while(row = mysql_fetch_row(result))
					{
						sprintf(IP[j], "%s", row[0]);
						j ++;
					}
					mysql_free_result(result);
				}
			}
			for(i=0; i<j; i++)
			{
				//sprintf(sqlstring, "INSERT INTO Command VALUE('FFFF','%s','0000000A01100000000102000900',0,NOW())",IP[i]);	
				sprintf(sqlstring, "INSERT INTO Command VALUE('0','%s','0000000A01100000000102000900',0,NOW())",IP[i]);
				
				if ( mysql_query(con, sqlstring))
					printf("Write RRROR %s", sqlstring);
			}
			les_timer = millis();
		}
		if(millis() - database_timer > 3000)	//Command 
		{
			// Maybe change
			j = 0;
			strcpy(sqlstring, "SELECT DISTINCT IP FROM Command where flag = 0 ");	//flag = 0  抓IP不重複並計算有幾個
			
			if (! mysql_query(con, sqlstring))
			{
				result = mysql_store_result(con);
			
				if(result != NULL)
				{
					while(row = mysql_fetch_row(result))
					{
						sprintf(IP[j], "%s", row[0]);
						
						#ifdef DEBUG
							printf("IP[%d] = %s\n", j, IP[j]);
						#endif
						j ++;
					}
					mysql_free_result(result);
				}
			}
			for(i=0; i<j; i++)
			{
				sprintf(sqlstring, "SELECT TS,OT FROM Command where flag = 0 && IP = '%s' order by TS  limit 1 ",IP[i]); //利用IP 抓flag = 0 並且只撈一筆
				#ifdef DEBUG
								printf("sqlstring = %s\n", sqlstring);
				#endif
				if (! mysql_query(con, sqlstring))
				{
					result = mysql_store_result(con);
					
					if(result != NULL)
					{
						
						while(row = mysql_fetch_row(result))
						{
							char tran[10];
							sprintf(tran, "%s",row[0]);
							//printf("rean:%s\n",tran);
							sprintf(TS[i], "%0*d%s",4-strlen(tran),0,tran);
							//printf("rean:%s\n",TS[i]);
							sprintf(OT[i], "%s", row[1]);
						}
						mysql_free_result(result);

						strcpy(Command, "AT+USOCKSEND=0,5678,\"");
						strcat(Command, IP[i]);
						strcat(Command, "\",");
						len = strlen(TS[i]) + strlen(OT[i]);
						sprintf(Wi_Sun, "%d", len);
						strcat(Command, Wi_Sun);
						strcat(Command, ",\"");
						strcat(Command, TS[i]);
						strcat(Command, OT[i]);
						strcat(Command, "\"");
						printf("send command = %s\n", Command);
						send_udp_packet_to_serial(serial_fd, Command);
					}
				}
			}
			database_timer = millis();
		}
		if(millis() - light_timer > 120000)	//一次詢問?
		{
			j = 0;
			strcpy(sqlstring, "SELECT DISTINCT IP FROM light");	
			
			if (! mysql_query(con, sqlstring))
			{
				result = mysql_store_result(con);
			
				if(result != NULL)
				{
					while(row = mysql_fetch_row(result))
					{
						sprintf(IP[j], "%s", row[0]);
						
						#ifdef DEBUG
							printf("IP[%d] = %s\n", j, IP[j]);
						#endif
						j ++;
					}
					mysql_free_result(result);
				}
			}
			for(i=0; i<j; i++)
			{
				sprintf(Command, "AT+USOCKSEND=0,5678,\"%s\",24,\"000000000006010300010005\"",IP[i]); //詢問所有資料
				#ifdef DEBUG
						printf("Command = %s\n", Command);
				#endif
				send_udp_packet_to_serial(serial_fd, Command);
			}
			light_timer = millis();
		}
	}
	mysql_close(con);
	close(serial_fd);
}
