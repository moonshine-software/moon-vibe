import './bootstrap';

document.addEventListener("DOMContentLoaded", function(): void {
    const user = new User('test', 1)
    console.log(user)
    console.log(user.getUserId())
});

class User {
    private readonly name: string

    private readonly userId: number

    constructor(name: string, userId: number) {
        this.name = name
        this.userId = userId
    }

    getName(): string {
        return this.name
    }

    getUserId(): number {
        return this.userId
    }
}