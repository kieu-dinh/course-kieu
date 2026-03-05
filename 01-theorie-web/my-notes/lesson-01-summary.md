# Lesson 01 Summary - Internet & Networks

## Key Concepts

### 1. Network
- Computers connected together to share information
- Your home network: Router connects your PC, phone, TV together

### 2. Internet
- A HUGE network of networks (worldwide)
- Your computer → Router → Internet Provider → Internet → Google's server

### 3. IP Address
- Like a **phone number** for computers
- Example: `142.250.76.238` (Google)
- Your computer has one: `192.168.100.170`
- Every computer needs one so others can send responses back

### 4. DNS (Domain Name System)
- Like a **phone book** - translates names to IP addresses
- You type: `google.com`
- DNS says: "That's `142.250.76.238`"
- Without DNS, you'd have to remember IP addresses!

### 5. Server
- A computer that **serves/provides things** to other computers
- Runs 24/7, waiting for requests
- Your computer = **client** (asks for things)
- Google's computer = **server** (gives things)

### 6. Ports
- Like **apartment numbers** in a building
- IP address = building address
- Port = which apartment (which service)

| Port | Service |
|------|---------|
| 80 | HTTP (regular websites) |
| 443 | HTTPS (secure websites) |
| 3306 | MySQL (database) |

---

## Extra Concepts We Discussed

### HTTP vs HTTPS
- **HTTP** (port 80) = plain text, not secure (like a postcard)
- **HTTPS** (port 443) = encrypted, secure (like a locked box)
- Always use HTTPS for passwords and sensitive data

### Database & SQL
- **Database** = where websites store data (like a powerful Excel)
- **MySQL** = popular database software
- **SQL** = language to talk to databases
- Will learn in Module 06

---

## Commands I Tried

```bash
# Find IP address for a domain
nslookup google.com

# Find my private IP address
ipconfig getifaddr en0
```

---

## Simple Diagram

```
YOU (Browser)                              SERVER
    |                                         |
    |  1. "Give me google.com"                |
    |  -------- uses DNS to find IP ------>   |
    |                                         |
    |  2. Connects to 142.250.76.238:443      |
    |  -------- HTTP REQUEST --------->       |
    |                                         |
    |  3. "Here's the webpage"                |
    |  <------- HTTP RESPONSE ---------       |
    |                                         |
    Browser shows the page!
```

---

## Quick Review Questions

1. What is DNS? → Phone book that translates domain names to IP addresses
2. What is an IP address? → A computer's address (like a phone number)
3. What is a server? → A computer that serves/provides things to others
4. What is port 443 for? → HTTPS (secure websites)
5. Why do you need an IP address? → So servers can send responses back to you

---

**Next:** Lesson 02 - HTTP Protocol (how computers actually "talk" to each other)
