# Lesson 01 - Internet & Networks

## What You'll Learn

- What a network is
- What the Internet is
- How computers find each other (IP addresses)
- How domain names work (DNS)

---

## What is a Network?

A **network** is simply computers connected together so they can share information.

### Simple Example

```
Computer A ----cable---- Computer B
```

This is a network! Two computers connected.

### Home Network

```
                    [Router/Box]
                    /    |    \
                   /     |     \
            Your PC   Phone   Smart TV
```

Your router connects all your devices together. They form a **local network** (LAN - Local Area Network).

### Key Point

A network = computers that can talk to each other.

---

## What is the Internet?

The **Internet** is simply a HUGE network of networks.

```
[Your home network] ---- [Internet Provider] ---- [Internet backbone] ---- [Google's network]
                                                                      |
                                                                      ---- [Facebook's network]
                                                                      |
                                                                      ---- [Millions of other networks]
```

When you visit google.com:
1. Your computer sends a message through your router
2. Your router sends it to your internet provider
3. Your provider routes it through the internet
4. It reaches Google's servers
5. Google sends back the webpage
6. It travels back to you

**The Internet is just computers talking to other computers across the world.**

---

## IP Addresses - How Computers Find Each Other

Every computer on the Internet has an **IP address**. It's like a phone number for computers.

### Format

IPv4 (old, still common):
```
192.168.1.1
216.58.214.206  (this was Google's IP)
```

IPv6 (new, more addresses):
```
2001:4860:4860::8888
```

### Your Computer's IP

Your computer actually has TWO IP addresses:

1. **Private IP** (inside your home network): like `192.168.1.100`
2. **Public IP** (on the Internet): assigned by your provider

### Try It!

Open your terminal and type:
```bash
# On Mac/Linux
ifconfig | grep "inet "

# Or on Mac, simpler:
ipconfig getifaddr en0
```

You'll see your private IP address!

---

## DNS - The Internet's Phone Book

IP addresses are hard to remember. Who wants to type `142.250.185.238` instead of `google.com`?

**DNS (Domain Name System)** translates human-readable names to IP addresses.

### How It Works

```
You type: google.com
    |
    v
Your browser asks DNS: "What's the IP for google.com?"
    |
    v
DNS responds: "It's 142.250.185.238"
    |
    v
Your browser connects to 142.250.185.238
    |
    v
Google's server responds with the webpage
```

### Try It!

Open your terminal:
```bash
# Ask DNS for Google's IP
nslookup google.com

# Or
dig google.com
```

You'll see the IP address(es) for google.com!

### DNS is Hierarchical

```
                    [Root DNS Servers]
                           |
            --------------------------------
            |              |               |
         [.com]         [.org]          [.fr]
            |              |               |
     [google.com]   [wikipedia.org]   [lemonde.fr]
```

1. Your browser asks your router's DNS
2. If it doesn't know, it asks your provider's DNS
3. If it doesn't know, it asks up the chain
4. Eventually, it finds the answer and caches it

---

## Ports - Multiple Services, One Computer

A single server can run multiple services. **Ports** separate them.

Think of it like an apartment building:
- IP address = building address
- Port = apartment number

### Common Ports

| Port | Service |
|------|---------|
| 80 | HTTP (websites) |
| 443 | HTTPS (secure websites) |
| 22 | SSH (remote terminal) |
| 3306 | MySQL (database) |
| 5432 | PostgreSQL (database) |

### When You Visit a Website

When you type `https://google.com`:
- Your browser connects to Google's IP
- On port **443** (HTTPS)

The full address is actually: `https://google.com:443`

The `:443` is hidden because it's the default for HTTPS.

---

## Summary

| Concept | What It Is | Analogy |
|---------|------------|---------|
| Network | Connected computers | Phones connected by phone lines |
| Internet | Network of networks | All phone networks connected |
| IP Address | Computer's address | Phone number |
| DNS | Name to IP translation | Phone book |
| Port | Service on a computer | Extension number |

---

## Quick Quiz

Answer these questions to check your understanding:

1. What is a network?
2. What is the Internet?
3. What does DNS do?
4. If a website is at `93.184.216.34`, what makes it easier to access?
5. What port is used for HTTPS?

<details>
<summary>Click to see answers</summary>

1. Computers connected together to share information
2. A giant network connecting all networks worldwide
3. Translates domain names (like google.com) to IP addresses
4. A domain name (like example.com) that DNS translates to that IP
5. Port 443

</details>

---

## Next Lesson

Now that you understand how computers find each other, let's learn HOW they communicate: [Lesson 02: HTTP Protocol](./02-http-requests.md)
