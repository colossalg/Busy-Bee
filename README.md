# Busy-Bee

Throw away project (todo tracker website using hand-rolled PHP libraries).

I created this website to test my PSR-7 and router implementations from these repositories:
- https://github.com/colossalg/Colossal-HTTP-Message
- https://github.com/colossalg/Colossal-Router

As well as to experiment with web-components.

The features supported are:
- Basing authentication (via a cruddy home-rolled session authentication); sign-up, sign-in, sign-out.
- Create, update and deletion of todo items (once the user is authenticated).

Everything seems to work and I think that I succeeded in verifying that my libraries work for a small project such as this.

I don't feel that my use of web-components was done correctly and was not satisfied with the state of the front-end code.

Experimenting with these features while also hand-rolling a crumby SPA from plain JS was a poor choice in hind-sight.

I still think they are a promising technology and will likely revisit them in future.

## Screenshots

| Description | Screenshot |
| --- | --- |
| Pre-sign-in | ![pre-sign-in](https://github.com/colossalg/Busy-Bee/assets/39691679/06a79d66-851f-4ca3-9f32-ce67cd9c827d) |
| Post-sign-in | ![post-sign-in](https://github.com/colossalg/Busy-Bee/assets/39691679/a4cd469c-b0c1-43f4-ae24-64dd2dde496b) |
| Sign-up modal | ![sign-up-modal](https://github.com/colossalg/Busy-Bee/assets/39691679/ca3812ac-4b57-4d70-b334-403e0adfcc37) |
| Sign-in modal | ![sign-in-modal](https://github.com/colossalg/Busy-Bee/assets/39691679/790bcd89-67fa-46ee-b34b-74af1e4dac52) |
| New todo-item modal | ![new-todo-item-modal](https://github.com/colossalg/Busy-Bee/assets/39691679/a2a8188c-ffe4-4fb3-be25-384875e99810) |
| Edit todo-item modal | ![edit-todo-item-modal](https://github.com/colossalg/Busy-Bee/assets/39691679/4feeb3d8-6049-428c-932e-28681961efc4) |





